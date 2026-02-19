<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendImageVersion
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $this->isHtmlResponse($response)) {
            return $response;
        }

        $content = $response->getContent();

        // Helper that appends a file-modified timestamp as a version query param
        $addVersion = function (string $url) {
            // Ignore external and data URIs
            if (preg_match('#^(?:https?:)?//#i', $url) || strpos($url, 'data:') === 0) {
                return $url;
            }

            // Normalize path portion
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            // Only handle local files that map to public/
            if (! str_starts_with($path, '/')) {
                $path = '/' . ltrim($path, '/');
            }

            $filePath = public_path(ltrim($path, '/'));
            if (! file_exists($filePath)) {
                return $url;
            }

            $version = filemtime($filePath);

            // Remove existing v= param to avoid duplicates
            $urlWithoutV = preg_replace('/([?&])v=[^&]*/', '$1', $url);
            $urlWithoutV = rtrim($urlWithoutV, '?&');

            $sep = (strpos($urlWithoutV, '?') === false) ? '?' : '&';

            return $urlWithoutV . $sep . 'v=' . $version;
        };

        // Replace src="..." and src='...'
        $content = preg_replace_callback('/\bsrc=("|\')([^"\']+)\1/i', function ($m) use ($addVersion) {
            $url = $m[2];
            $new = $addVersion($url);
            return 'src=' . $m[1] . $new . $m[1];
        }, $content);

        // Replace srcset="..." with multiple comma-separated URLs
        $content = preg_replace_callback('/\bsrcset=("|\')([^"\']+)\1/i', function ($m) use ($addVersion) {
            $parts = array_map('trim', explode(',', $m[2]));
            $newParts = [];
            foreach ($parts as $part) {
                if (preg_match('/\s+/', $part)) {
                    list($u, $desc) = preg_split('/\s+/', $part, 2);
                    $newParts[] = $addVersion($u) . ' ' . $desc;
                } else {
                    $newParts[] = $addVersion($part);
                }
            }
            return 'srcset=' . $m[1] . implode(', ', $newParts) . $m[1];
        }, $content);

        // Replace url(...) occurrences in inline styles
        $content = preg_replace_callback('/url\(("|\')?([^)"\']+)("|\')?\)/i', function ($m) use ($addVersion) {
            $url = $m[2];
            $new = $addVersion($url);
            $quote = $m[1] ?: '';
            return 'url(' . $quote . $new . $quote . ')';
        }, $content);

        $response->setContent($content);

        return $response;
    }

    protected function isHtmlResponse($response): bool
    {
        if (! method_exists($response, 'headers')) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type') ?? '';
        return stripos($contentType, 'text/html') !== false || stripos($contentType, 'application/xhtml+xml') !== false;
    }
}
