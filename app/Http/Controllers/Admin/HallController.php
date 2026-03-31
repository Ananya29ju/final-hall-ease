<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\HallImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HallController extends Controller
{
    /**
     * Display a listing of halls
     */
    public function index(Request $request)
    {
        $query = Hall::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('campus_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
        }

        $halls = $query->paginate(10)->withQueryString();
        return view('admin.halls.index', compact('halls'));
    }

    /**
     * Display campus/block navigation for admin.
     */
    public function browse()
    {
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

        return view('admin.halls.browse', [
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

        return view('admin.halls.block-halls', [
            'campus' => $campus,
            'block' => $block,
            'halls' => $halls,
        ]);
    }

    /**
     * Show the form for creating a new hall
     */
    public function create()
    {
        return view('admin.halls.create');
    }

    /**
     * Store a newly created hall in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campus_name' => 'required|in:Main Campus,AIMIT Campus,Engineering Campus,Capitano Campus',
            'location' => 'required|in:Admin,Aruppe,Maffie,Xavier,LCRI',
            'capacity' => 'required|integer|min:10',
            'description' => 'nullable|string',
            'status' => 'in:available,maintenance',
            'hall_images' => 'nullable|array|max:20',
            'hall_images.*' => 'file|mimes:jpg,jpeg,png,webp,heic,heif|max:10240',
        ]);

        $hall = Hall::create([
            'name' => $validated['name'],
            'campus_name' => $validated['campus_name'],
            'location' => $validated['location'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'available',
        ]);

        if ($request->hasFile('hall_images')) {
            foreach ($request->file('hall_images') as $image) {
                $path = $image->store('halls', 'public');

                HallImage::create([
                    'hall_id' => $hall->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('admin.halls.index')
            ->with('success', 'Hall created successfully!');
    }

    /**
     * Display the specified hall
     */
    public function show(Hall $hall)
    {
        $images = $hall->images;
        return view('admin.halls.show', compact('hall', 'images'));
    }

    /**
     * Show the form for editing the specified hall
     */
    public function edit(Hall $hall)
    {
        return view('admin.halls.edit', compact('hall'));
    }

    /**
     * Delete a single hall image.
     */
    public function destroyImage(Hall $hall, HallImage $image)
    {
        if ((int) $image->hall_id !== (int) $hall->id) {
            return back()->with('error', 'The selected image does not belong to this hall.');
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Hall image deleted successfully.');
    }

    /**
     * Update the specified hall in database
     */
    public function update(Request $request, Hall $hall)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campus_name' => 'required|in:Main Campus,AIMIT Campus,Engineering Campus,Capitano Campus',
            'location' => 'required|in:Admin,Aruppe,Maffie,Xavier,LCRI',
            'capacity' => 'required|integer|min:10',
            'description' => 'nullable|string',
            'status' => 'in:available,maintenance',
            'hall_images' => 'nullable|array|max:20',
            'hall_images.*' => 'file|mimes:jpg,jpeg,png,webp,heic,heif|max:10240',
        ]);

        $hall->update([
            'name' => $validated['name'],
            'campus_name' => $validated['campus_name'],
            'location' => $validated['location'],
            'capacity' => $validated['capacity'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'available',
        ]);

        if ($request->hasFile('hall_images')) {
            foreach ($request->file('hall_images') as $image) {
                $path = $image->store('halls', 'public');

                HallImage::create([
                    'hall_id' => $hall->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('admin.halls.index')
            ->with('success', 'Hall updated successfully!');
    }

    /**
     * Remove the specified hall from database
     */
    public function destroy(Hall $hall)
    {
        foreach ($hall->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $hall->delete();

        return redirect()
            ->route('admin.halls.index')
            ->with('success', 'Hall deleted successfully!');
    }
}
