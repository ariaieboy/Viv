<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Release;
use App\Changelog;
use Parsedown;

class TimelineController extends Controller
{
    public function index(Request $request) {
        $platform_id = getPlatformIdByClass($request->platform);
        $ring_id = getRingIdByClass($request->ring);
        $timeline = [];

        $releases = Release::select('releases.*', 'milestones.color', 'milestones.version')
                            ->when(request('ring', false), function ($query, $ring_id) {
                                return $query->where('ring', getRingIdByClass($ring_id));
                            })->when(request('platform', false), function ($query, $platform_id) {
                                return $query->where('platform', getPlatformIdByClass($platform_id));
                            })->join('milestones', 'milestones.id', '=', 'releases.milestone')
                            ->orderBy('date', 'desc')
                            ->orderBy('build', 'desc')
                            ->orderBy('platform', 'asc')
                            ->orderBy('delta', 'desc')
                            ->orderBy('ring', 'desc')
                            ->paginate(75)
                            ->onEachSide(1);

        // TODO: This is probably the crappiest piece of code in all of Viv
        foreach ($releases as $release) {
            $timeline[$release->date->format('j F Y')][$release->build][$release->delta][$release->platform][$release->ring] = $release;
            $timeline[$release->date->format('j F Y')][$release->build][$release->delta][$release->platform]['default'] = $release;
        }

        $flights['pc']['fast'] = Release::pc()->active()->latestFlight()->first();
        $flights['pc']['slow'] = Release::pc()->slow()->latestFlight()->first();
        $flights['pc']['release'] = Release::pc()->release()->latestFlight()->first();
        $flights['pc']['targeted'] = Release::pc()->targeted()->latestFlight()->first();
        $flights['pc']['broad'] = Release::pc()->broad()->latestFlight()->first();
        $flights['pc']['ltsc'] = Release::pc()->ltsc()->latestFlight()->first();

        $flights['xbox']['skip'] = Release::xbox()->skip()->latestFlight()->first();
        $flights['xbox']['fast'] = Release::xbox()->active()->latestFlight()->first();
        $flights['xbox']['slow'] = Release::xbox()->slow()->latestFlight()->first();
        $flights['xbox']['preview'] = Release::xbox()->preview()->latestFlight()->first();
        $flights['xbox']['release'] = Release::xbox()->release()->latestFlight()->first();
        $flights['xbox']['targeted'] = Release::xbox()->targeted()->latestFlight()->first();

        $flights['server']['slow'] = Release::server()->slow()->latestFlight()->first();
        $flights['server']['targeted'] = Release::server()->targeted()->latestFlight()->first();
        $flights['server']['ltsc'] = Release::server()->ltsc()->latestFlight()->first();

        $flights['iot']['targeted'] = Release::iot()->targeted()->latestFlight()->first();
        $flights['iot']['broad'] = Release::iot()->broad()->latestFlight()->first();

        $flights['holo']['fast'] = Release::holographic()->active()->latestFlight()->first();
        $flights['holo']['slow'] = Release::holographic()->slow()->latestFlight()->first();
        $flights['holo']['targeted'] = Release::holographic()->targeted()->latestFlight()->first();
        $flights['holo']['broad'] = Release::holographic()->broad()->latestFlight()->first();
        $flights['holo']['ltsc'] = Release::holographic()->ltsc()->latestFlight()->first();

        $flights['team']['fast'] = Release::team()->active()->latestFlight()->first();
        $flights['team']['slow'] = Release::team()->slow()->latestFlight()->first();
        $flights['team']['targeted'] = Release::team()->targeted()->latestFlight()->first();
        $flights['team']['broad'] = Release::team()->broad()->latestFlight()->first();

        $flights['sdk']['targeted'] = Release::sdk()->targeted()->latestFlight()->first();
        $flights['iso']['targeted'] = Release::iso()->targeted()->latestFlight()->first();

        return view('timeline', compact('releases', 'flights', 'timeline', 'request'));
    }

    public function show($build, $platform = null) {
        $cur_build = $build;
        $platform_id = $platform === null ? 1 : getPlatformIdByClass($platform);

        $platforms = Release::select('platform')->where('build', $cur_build)->orderBy('platform', 'asc')->distinct()->get();

        if ($platforms->count() < 1) {
            abort(404);
        }

        if ($platform) {
            $releases = Release::where('build', $cur_build)->where('platform', $platform_id)->orderBy('date', 'asc')->orderBy('delta', 'asc')->orderBy('ring', 'asc')->get();

            if ($releases->count() < 1) {
                $platform_id = $platforms[0]->platform;
                $releases = Release::where('build', $cur_build)->where('platform', $platforms[0]->platform)->orderBy('date', 'asc')->orderBy('delta', 'asc')->orderBy('ring', 'asc')->paginate(50);
            }
        } else {
            $platform_id = $platforms[0]->platform;
            $releases = Release::where('build', $cur_build)->orderBy('date', 'asc')->orderBy('delta', 'asc')->orderBy('ring', 'asc')->paginate(50);
        }

        $milestone = $releases[0]->ms;

        $changelogs = Changelog::where('build', $cur_build)->where('platform', $platform_id)->orWhere('build', $cur_build)->where('platform', '0')->orderBy('platform', 'desc')->get()->keyBy('delta');

        $meta = Release::where('build', $cur_build)->where('platform', $platform_id)->first();

        foreach ($releases as $release) {
            $timeline[$release->date->format('j F Y')][$release->build][$release->delta][$release->platform][$release->ring] = $release;
            $notes[$release->delta]['rings'][$release->ring] = $release;
        }

        foreach ($changelogs as $changelog) {
            if (array_key_exists($changelog->delta, $notes)) {
                $notes[$changelog->delta]['changelog'] = $changelog->changelog;
                $notes[$changelog->delta]['created'] = $changelog->created_at;
                $notes[$changelog->delta]['new'] = $changelog->created_at->addDay();
            }
        }

        $previous = Release::where('build', '<', $cur_build)->orderBy('build', 'desc')->first();
        $next = Release::where('build', '>', $cur_build)->orderBy('build', 'asc')->first();

        $parsedown = new Parsedown();

        return view('build', compact('timeline', 'platforms', 'notes', 'meta', 'cur_build', 'parsedown', 'milestone', 'next', 'previous'));
    }
}
