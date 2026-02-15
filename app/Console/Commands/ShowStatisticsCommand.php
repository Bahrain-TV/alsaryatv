<?php

namespace App\Console\Commands;

use App\Mail\StatisticsReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ShowStatisticsCommand extends Command
{
    // Update command signature to include email and OBS overlay options
    protected $signature = 'app:show:stats {--from=} {--to=} {--email=} {--skip-email-prompt} {--overlay : Refresh the OBS overlay layer after generating statistics}';

    protected $description = 'Show statistical report about registered callers on OBS and save it as a markdown file. Optionally send the report by email.';

    protected $dateFilter = '';

    // Default email addresses
    protected $defaultEmails = [
        'aldoyh@gmail.com',
        'alsaryatv@gmail.com',
    ];

    public function handle(): string
    {
        $this->dateFilter = $this->getDateFilter();

        $now = now()->format('Y-m-d H:i:s');
        $markdown = "# السارية TV - تقرير الإحصائيات\n\n";
        $markdown .= "تم إنشاء التقرير في: {$now}\n\n";

        $data = [
            'stats' => $this->getStats(),
            'dailyStats' => $this->getDailyStats(),
            'hourlyStats' => $this->showTimeBasedAnalysis(),
            'statusDistribution' => $this->showStatusDistribution(),
            'period' => $this->showRegistrationPeriod(),
            'winnerStats' => $this->getWinnerStats(),
        ];

        // Handle potential null values from database queries
        if ($data['period']) {
            $markdown .= $this->generatePeriodSection($data['period']);
        }

        if ($data['stats']) {
            $markdown .= $this->generateBasicStatsSection($data['stats']);
        }

        if ($data['winnerStats']) {
            $markdown .= $this->generateWinnerAnalysisSection($data['winnerStats'], $data['stats']['totalCallers']);
        }

        if ($data['hourlyStats']) {
            $markdown .= $this->generateTimeAnalysisSection($data['hourlyStats']);
        }

        if ($data['statusDistribution']) {
            $markdown .= $this->generateStatusDistributionSection($data['statusDistribution']);
        }

        if ($data['dailyStats']) {
            $markdown .= $this->generateDailyStatsSection($data['dailyStats']);
        }

        // Footer
        $markdown .= "\n---\n\n";
        $markdown .= "تم إنشاء هذا التقرير تلقائياً بواسطة نظام السارية TV للإحصائيات\n";
        $markdown .= "التاريخ: {$now}\n\n";
        $markdown .= '![السارية TV]('.asset('storage/images/alsarya-logo-2024.png').")\n";

        // Create directory if it doesn't exist
        Storage::makeDirectory('statistics', 0775, true);

        // Save file
        $filename = 'statistics/report-'.now()->format('Y-m-d-His').'.md';
        Storage::put($filename, $markdown);

        $this->info('Statistics report generated successfully: '.$filename);
        $this->info('Statistics report URL: '.Storage::url($filename));

        // spit out the full report
        $this->info($markdown);

        // Handle email sending based on command options
        $this->handleEmailSending($filename, $markdown);

        // Handle OBS overlay refresh if requested
        if ($this->option('overlay')) {
            $this->triggerOverlayRefresh();
        }

        return $filename;
    }

    /**
     * Trigger the OBS overlay to refresh by clearing cache and logging
     */
    protected function triggerOverlayRefresh(): void
    {
        try {
            // Clear stats cache to force refresh on next poll
            cache()->forget('obs-overlay-stats');

            // Set a flag to indicate stats were updated
            cache()->put('stats-updated-at', now()->toIso8601String(), now()->addMinutes(5));

            $this->info('OBS overlay refresh triggered successfully.');
        } catch (\Exception $e) {
            $this->warn('Could not trigger OBS overlay refresh: '.$e->getMessage());
        }
    }

    /**
     * Handle email sending based on user input
     */
    protected function handleEmailSending(string $filename, string $reportContent): void
    {
        // If --email option is provided, use that value
        if ($this->option('email')) {
            $emails = explode(',', $this->option('email'));
            $this->sendEmailsToRecipients($emails, $filename, $reportContent);

            return;
        }

        // Skip prompt if explicitly requested
        if ($this->option('skip-email-prompt')) {
            return;
        }

        // If no date arguments provided, prompt for each default email
        if (! $this->option('from') && ! $this->option('to')) {
            $this->info('No date arguments provided. Would you like to send this report by email?');

            $emailsToSend = [];

            // Ask about each default email address using built-in confirm()
            foreach ($this->defaultEmails as $email) {
                if ($this->confirm("Send a copy to {$email}?", true)) {
                    $emailsToSend[] = $email;
                }
            }

            // Ask if user wants to add additional email addresses
            if ($this->confirm('Would you like to add any additional email addresses?', false)) {
                $additionalEmail = $this->ask('Enter additional email address (or leave blank to skip)');
                if (! empty($additionalEmail) && filter_var($additionalEmail, FILTER_VALIDATE_EMAIL)) {
                    $emailsToSend[] = $additionalEmail;
                } elseif (! empty($additionalEmail)) {
                    $this->error('Invalid email address provided. Skipping additional email.');
                }
            }

            // Send emails if recipients selected
            if (! empty($emailsToSend)) {
                $this->sendEmailsToRecipients($emailsToSend, $filename, $reportContent);
            }
        }
    }

    /**
     * Send emails to specified recipients
     */
    protected function sendEmailsToRecipients(array $emails, string $filename, string $reportContent): void
    {
        foreach ($emails as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $this->info("Sending report to {$email}...");
                    // Create report email with attachment
                    Mail::to($email)->send(new StatisticsReport($reportContent, $filename));
                    $this->info("Email sent successfully to {$email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send email to {$email}: {$e->getMessage()}");
                }
            } else {
                $this->error("Invalid email address: {$email}");
            }
        }
    }

    protected function getDateFilter(): string
    {
        $from = $this->option('from');
        $to = $this->option('to');

        $filter = '';
        if ($from) {
            $filter .= " AND created_at >= '".Carbon::parse($from)->toDateString()."'";
        }
        if ($to) {
            $filter .= " AND created_at <= '".Carbon::parse($to)->toDateString()."'";
        }

        return $filter;
    }

    protected function getStats(): ?array
    {
        try {
            $totalCallers = DB::table('callers')->count();
            $individualCallers = $totalCallers;
            $familyCallers = 0;
            $winnersCount = DB::table('callers')->where('is_winner', true)->count();
            $totalHits = DB::table('callers')->sum('hits');

            return [
                'totalCallers' => $totalCallers,
                'individualCallers' => $individualCallers,
                'familyCallers' => $familyCallers,
                'winnersCount' => $winnersCount,
                'totalHits' => $totalHits,
                'avgHitsPerCaller' => $totalCallers > 0 ? round($totalHits / $totalCallers, 2) : 0,
            ];
        } catch (\Exception $e) {
            $this->error('Error getting Stats: '.$e->getMessage());

            return null;
        }
    }

    protected function getDailyStats(): ?Collection
    {
        try {
            return collect(DB::select("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM callers
                WHERE 1=1 {$this->dateFilter}
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            "));
        } catch (\Exception $e) {
            $this->error('Error getting daily stats: '.$e->getMessage());

            return null;
        }
    }

    protected function showTimeBasedAnalysis(): ?Collection
    {
        try {
            return collect(DB::select("
                SELECT HOUR(created_at) as hour, COUNT(*) as count
                FROM callers
                WHERE 1=1 {$this->dateFilter}
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC
            "));
        } catch (\Exception $e) {
            $this->error('Error getting time-based analysis: '.$e->getMessage());

            return null;
        }
    }

    protected function showStatusDistribution(): ?Collection
    {
        try {
            return collect(DB::select("
                SELECT status, COUNT(*) as count
                FROM callers
                WHERE 1=1 {$this->dateFilter}
                GROUP BY status
                ORDER BY count DESC
            "));
        } catch (\Exception $e) {
            $this->error('Error getting status distribution: '.$e->getMessage());

            return null;
        }
    }

    protected function showRegistrationPeriod(): ?object
    {
        try {
            return DB::selectOne("
                SELECT 
                    MIN(created_at) as first_registration,
                    MAX(created_at) as last_registration
                FROM callers
                WHERE 1=1 {$this->dateFilter}
            ");
        } catch (\Exception $e) {
            $this->error('Error getting registration period: '.$e->getMessage());

            return null;
        }
    }

    protected function getWinnerStats(): ?object
    {
        try {
            return DB::table('callers')
                ->where('is_winner', true)
                ->whereRaw("1=1 {$this->dateFilter}") // Added date filter to winner stats query.
                ->select(
                    DB::raw('COUNT(*) as total_winners'),
                    DB::raw('AVG(hits) as avg_hits'),
                    DB::raw('MIN(hits) as min_hits'),
                    DB::raw('MAX(hits) as max_hits')
                )
                ->first();
        } catch (\Exception $e) {
            $this->error('Error getting Winner Stats: '.$e->getMessage());

            return null;
        }
    }

    protected function generateBasicStatsSection(array $data): string
    {
        $markdown = "## 📊 الإحصائيات الأساسية\n\n";
        $markdown .= "### 👥 المشاركين\n\n";
        $markdown .= "* إجمالي عدد المشاركين: **{$data['totalCallers']}**\n";
        $markdown .= "* المشاركين الأفراد: **{$data['individualCallers']}**\n";
        $markdown .= "* الفائزين: **{$data['winnersCount']}**\n\n";
        $markdown .= "### 🎯 الضغطات\n\n";
        $markdown .= "* إجمالي عدد الضغطات: **{$data['totalHits']}**\n";
        $markdown .= "* متوسط الضغطات لكل مشارك: **{$data['avgHitsPerCaller']}**\n\n";

        return $markdown;
    }

    protected function generateWinnerAnalysisSection(object $winnerStats, int $totalCallers): string
    {
        $winnerPercentage = $totalCallers > 0 ? round(($winnerStats->total_winners / $totalCallers) * 100, 2) : 0;
        $markdown = "## 🏆 تحليل الفائزين\n\n";
        $markdown .= "* عدد الفائزين: **{$winnerStats->total_winners}**\n";
        $markdown .= '* متوسط ضغطات الفائزين: **'.round($winnerStats->avg_hits, 2)."**\n";
        $markdown .= "* أقل عدد ضغطات للفائز: **{$winnerStats->min_hits}**\n";
        $markdown .= "* أعلى عدد ضغطات للفائز: **{$winnerStats->max_hits}**\n";
        $markdown .= "* نسبة الفائزين: **{$winnerPercentage}%**\n\n";  // enhanced: display winners percentage

        return $markdown;
    }

    protected function generateTimeAnalysisSection(Collection $hourlyStats): string
    {
        $markdown = "## ⏰ التوزيع حسب الساعات\n\n";
        $markdown .= "| الساعة | عدد التسجيلات |\n";
        $markdown .= "|--------|----------------|\n";

        foreach ($hourlyStats as $stat) {
            $markdown .= "| {$stat->hour} | {$stat->count} |\n";
        }

        return $markdown;
    }

    protected function generateStatusDistributionSection(Collection $statusDistribution): string
    {
        $markdown = "## 📊 توزيع الحالات\n\n";
        $markdown .= "| الحالة | عدد المشاركات |\n";
        $markdown .= "|--------|----------------|\n";

        foreach ($statusDistribution as $stat) {
            $markdown .= "| {$stat->status} | {$stat->count} |\n";
        }

        return $markdown;
    }

    protected function generateDailyStatsSection(Collection $dailyStats): string
    {
        $markdown = "## 📊 الإحصائيات اليومية\n\n";
        $markdown .= "| التاريخ | عدد التسجيلات |\n";
        $markdown .= "|--------|----------------|\n";

        foreach ($dailyStats as $stat) {
            $markdown .= "| {$stat->date} | {$stat->count} |\n";
        }

        return $markdown;
    }

    protected function generatePeriodSection(object $period): string
    {
        $markdown = "## 📅 فترة التسجيل\n\n";
        $markdown .= '* أول تسجيل: '.Carbon::parse($period->first_registration)->format('Y-m-d H:i:s')."\n";
        $markdown .= '* آخر تسجيل: '.Carbon::parse($period->last_registration)->format('Y-m-d H:i:s')."\n";
        $markdown .= '* المدة: '.Carbon::parse($period->last_registration)->diffForHumans(Carbon::parse($period->first_registration))."\n\n";

        return $markdown;
    }
}
