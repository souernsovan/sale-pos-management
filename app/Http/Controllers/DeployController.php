<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * A browser-triggerable stand-in for `php artisan config:cache` etc. — for
 * hosts (like InfinityFree) with no terminal/SSH access, where those
 * commands otherwise can't be run at all after uploading new files.
 */
class DeployController extends Controller
{
    public function optimize(Request $request): Response
    {
        $this->authorize($request);

        $output = $this->run(['config:clear', 'cache:clear', 'route:clear', 'view:clear', 'config:cache', 'route:cache', 'view:cache']);

        return $this->plainText("Optimized.\n\n{$output}");
    }

    /**
     * Escape hatch: undo a bad cache without needing a terminal.
     */
    public function clear(Request $request): Response
    {
        $this->authorize($request);

        $output = $this->run(['config:clear', 'cache:clear', 'route:clear', 'view:clear']);

        return $this->plainText("Cleared.\n\n{$output}");
    }

    private function authorize(Request $request): void
    {
        $expected = config('services.deploy_token');

        abort_if(! $expected, 404);
        abort_unless(hash_equals((string) $expected, (string) $request->query('token')), 403);
    }

    private function run(array $commands): string
    {
        $output = '';

        foreach ($commands as $command) {
            Artisan::call($command);
            $output .= "\$ php artisan {$command}\n".Artisan::output()."\n";
        }

        return $output;
    }

    private function plainText(string $body): Response
    {
        return response($body)->header('Content-Type', 'text/plain');
    }
}
