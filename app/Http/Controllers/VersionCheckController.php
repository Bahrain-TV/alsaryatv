<?php

namespace App\Http\Controllers;

use App\Services\VersionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionCheckController extends Controller
{
    /**
     * Get current version information
     *
     * @return JsonResponse
     */
    public function getVersion(Request $request): JsonResponse
    {
        $currentVersion = VersionManager::getVersionInfo();
        $branch = VersionManager::getBranch();
        $commitHash = VersionManager::getCommitHash();

        return response()->json([
            'success' => true,
            'current' => [
                'version' => $currentVersion['version'],
                'branch' => $branch,
                'commit' => $commitHash,
                'updated_at' => $currentVersion['updated_at'],
                'environment' => app()->environment(),
            ],
            'metadata' => [
                'name' => $currentVersion['name'],
                'description' => $currentVersion['description'],
            ],
        ]);
    }

    /**
     * Check for version differences between local and remote
     * Used by development environments to detect production changes
     *
     * @return JsonResponse
     */
    public function checkVersionDifference(Request $request): JsonResponse
    {
        $request->validate([
            'remote_version' => 'required|string',
            'remote_branch' => 'nullable|string',
            'notify_on_difference' => 'nullable|boolean',
        ]);

        $localVersion = VersionManager::getVersion();
        $remoteVersion = $request->input('remote_version');
        $remoteBranch = $request->input('remote_branch');
        $localBranch = VersionManager::getBranch();

        // Compare versions
        $versionComparison = version_compare($remoteVersion, $localVersion);
        $hasDifference = $versionComparison !== 0;
        $branchDifference = $remoteBranch && $remoteBranch !== $localBranch;

        $response = [
            'success' => true,
            'has_difference' => $hasDifference || $branchDifference,
            'comparison' => [
                'local_version' => $localVersion,
                'remote_version' => $remoteVersion,
                'version_status' => $this->getVersionStatus($versionComparison),
                'local_branch' => $localBranch,
                'remote_branch' => $remoteBranch,
                'branch_match' => $localBranch === $remoteBranch,
            ],
            'notification' => null,
        ];

        // Add notification if requested and difference exists
        if ($request->boolean('notify_on_difference') && $hasDifference) {
            $response['notification'] = $this->buildNotification($remoteVersion, $localVersion, $remoteBranch, $localBranch);

            // Log the notification for audit trail
            \Illuminate\Support\Facades\Log::info('Version mismatch detected', [
                'local_version' => $localVersion,
                'remote_version' => $remoteVersion,
                'local_branch' => $localBranch,
                'remote_branch' => $remoteBranch,
                'client_ip' => $request->ip(),
            ]);
        }

        return response()->json($response);
    }

    /**
     * Get version changelog
     *
     * @return JsonResponse
     */
    public function getChangeLog(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $changelog = VersionManager::getChangeLog();
        $limit = $request->integer('limit', 20);

        return response()->json([
            'success' => true,
            'changelog' => array_slice($changelog, 0, $limit),
            'total' => count($changelog),
        ]);
    }

    /**
     * Increment version (admin only)
     *
     * @return JsonResponse
     */
    public function incrementVersion(Request $request): JsonResponse
    {
        $this->authorize('isAdmin'); // Ensure user is admin

        $request->validate([
            'type' => 'required|in:major,minor,patch',
        ]);

        $type = $request->input('type');
        $newVersion = match ($type) {
            'major' => VersionManager::incrementMajor(),
            'minor' => VersionManager::incrementMinor(),
            'patch' => VersionManager::incrementPatch(),
        };

        \Illuminate\Support\Facades\Log::warning('Version incremented by user', [
            'user' => auth()->user()?->email,
            'type' => $type,
            'new_version' => $newVersion,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Version incremented to {$newVersion}",
            'new_version' => $newVersion,
            'type' => $type,
        ]);
    }

    /**
     * Private: Get human-readable version status
     */
    private function getVersionStatus(int $comparison): string
    {
        return match ($comparison) {
            -1 => 'outdated', // remote is newer
            0 => 'up-to-date',
            1 => 'ahead', // local is newer (unusual but possible)
        };
    }

    /**
     * Private: Build notification message
     */
    private function buildNotification(string $remoteVersion, string $localVersion, ?string $remoteBranch, string $localBranch): array
    {
        $versionStatus = version_compare($remoteVersion, $localVersion) > 0 ? 'outdated' : 'ahead';

        if ($versionStatus === 'outdated') {
            return [
                'type' => 'update_available',
                'title' => 'Update Available',
                'message' => "A new version ({$remoteVersion}) is available on {$remoteBranch}. Your local version is {$localVersion}.",
                'severity' => 'info',
                'action' => 'Pull latest changes from ' . ($remoteBranch ?? 'remote'),
                'local_version' => $localVersion,
                'remote_version' => $remoteVersion,
                'remote_branch' => $remoteBranch,
            ];
        } else {
            return [
                'type' => 'version_ahead',
                'title' => 'Local Version Ahead',
                'message' => "Your local version ({$localVersion}) is ahead of production ({$remoteVersion}).",
                'severity' => 'warning',
                'action' => 'Ensure all changes are committed and pushed',
            ];
        }
    }
}
