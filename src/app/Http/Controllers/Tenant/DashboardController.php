<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\OnboardingSession;
use App\Models\Tenant\Session;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::check()) {

            /** @var Tenant $tenant */
            $tenant = tenant();

            /** @var User $user */
            $user = Auth::user();

            return Inertia::render('Dashboard', [
                'members' => $user->members()->with(['squads' => function ($query) {
                    $query->orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc');
                }])->get(),
                'sessions' => $user->hasPermission(['Admin', 'Coach']) ? Session::where('SessionDay', '=', now()->dayOfWeek)
                    ->where('DisplayFrom', '<=', now()->toDateString())
                    ->where('DisplayUntil', '>=', now()->toDateString())
                    ->where('StartTime', '>=', now()->subHours(1)->toTimeString())
                    ->where('EndTime', '<=', now()->addHours(2)->toTimeString())
                    ->get()->map(function (Session $item) {
                        return [
                            'SessionID' => $item->SessionID,
                            'SessionName' => $item->SessionName,
                            'StartTime' => $item->StartTime,
                            'EndTime' => $item->EndTime,
                            'StartDateTime' => (new \DateTime($item->StartTime, new \DateTimeZone('Europe/London')))->format('c'),
                            'EndDateTime' => (new \DateTime($item->EndTime, new \DateTimeZone('Europe/London')))->format('c'),
                        ];
                    }) : [],
                'onboarding_sessions' => $user->onboardingSessions()
                    ->where(function (Builder $query) {
                        return $query->where('status', 'pending')->orWhere('status', 'in_progress');
                    })
                    ->get()
                    ->map(function (OnboardingSession $item) {
                        return [
                            'id' => $item->id,
                        ];
                    }),
                'swim_england_news' => Cache::remember('swim_england_news', 3600 * 3, function () {
                    try {
                        $data = [];
                        $response = Http::get('https://www.swimming.org/sport/wp-json/wp/v2/posts?per_page=6')->json();
                        foreach ($response as $item) {
                            $data[] = [
                                'id' => $item['id'],
                                'title' => $item['title']['rendered'],
                                'link' => $item['link'],
                                'date' => $item['date_gmt'],
                            ];
                        }

                        return $data;
                    } catch (\Exception $e) {
                        return [];
                    }
                }),
                'regional_news' => Cache::remember('regional_news', 3600 * 3, function () {
                    try {
                        $data = [];
                        $response = Http::get('https://asaner.org.uk/feed')->body();
                        $response = new \SimpleXMLElement($response);

                        for ($i = 0; $i < min(count($response->channel->item), 6); $i++) {
                            $data[] = [
                                'id' => (string) $response->channel->item[$i]->guid,
                                'title' => (string) $response->channel->item[$i]->title,
                                'link' => (string) $response->channel->item[$i]->link,
                                'date' => (string) $response->channel->item[$i]->pubDate,
                            ];
                        }

                        return $data;
                    } catch (\Exception $e) {
                        return [];
                    }
                }),
                'now' => now()->toDateString(),
                'gocardless_switch_off' => Auth::user()->hasPermission('Admin') &&
                    $tenant->getOption('GOCARDLESS_ACCESS_TOKEN') && ! $tenant->getOption('USE_STRIPE_DIRECT_DEBIT'),
            ]);
        }

        return Inertia::render('Index');
    }
}
