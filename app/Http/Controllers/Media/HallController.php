<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\Hall;

class HallController extends Controller
{
    /**
     * Display campus/block navigation for users.
     */
    public function browse(\Illuminate\Http\Request $request)
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

            return view('media.halls.block-halls', [
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

        return view('media.halls.browse', [
            'campusGroups' => $campusGroups,
        ]);
    }

    /**
     * Display halls for a specific campus block.
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

        return view('media.halls.block-halls', [
            'campus' => $campus,
            'block' => $block,
            'halls' => $halls,
        ]);
    }
}
