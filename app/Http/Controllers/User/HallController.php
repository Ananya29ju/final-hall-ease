<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Hall;

/**
 * HallController (User)
 * 
 * Responsible for handling the display, browsing, and searching of halls 
 * for regular users (staff) on the frontend platform.
 */
class HallController extends Controller
{
    /**
     * Display campus and block navigation for users, or process a search query.
     * 
     * If a 'search' term is provided in the request, it searches halls by name,
     * location, or campus. Otherwise, it groups the halls hierarchically by
     * campus name and location blocks for easy navigation.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(\Illuminate\Http\Request $request)
    {
        if ($request->filled('search')) {
            $halls = Hall::query()
                ->with('images')
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('location', 'like', '%' . $request->search . '%')
                ->orWhere('campus_name', 'like', '%' . $request->search . '%')
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString();

            return view('user.block-halls', [
                'campus' => 'Search Results',
                'block' => "for '" . $request->search . "'",
                'halls' => $halls,
            ]);
        }

        $campusGroups = Hall::query()
            ->select('campus_name', 'location')
            ->whereNotNull('campus_name')
            ->where('campus_name', '!=', '')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->orderBy('campus_name')
            ->orderBy('location')
            ->get()
            ->groupBy('campus_name')
            ->map(function ($rows) {
                return $rows->pluck('location')->unique()->values();
            });

        return view('user.halls.index', [
            'campusGroups' => $campusGroups,
        ]);
    }

    /**
     * Display halls that belong to a specific campus and block.
     * 
     * Retrieves paginated halls matching the exact campus and location (block)
     * provided in the URL parameters.
     * 
     * @param string $campus The name of the campus.
     * @param string $block The location block within the campus.
     * @return \Illuminate\View\View
     */
    public function block(string $campus, string $block)
    {
        $halls = Hall::query()
            ->with('images')
            ->where('campus_name', $campus)
            ->where('location', $block)
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('user.block-halls', [
            'campus' => $campus,
            'block' => $block,
            'halls' => $halls,
        ]);
    }
}
