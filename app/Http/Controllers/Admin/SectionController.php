<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MenuSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $sections = MenuSection::ordered()->withCount('menus')->get();

        return view('admin.sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('admin.sections.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:menu_sections,name'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $section = MenuSection::create($validated);

        ActivityLog::record('created', $section, "Menambah section: {$section->name}");

        return redirect()->route('admin.sections.index')->with('success', "Section \"{$section->name}\" berhasil ditambahkan.");
    }

    public function edit(MenuSection $section): View
    {
        return view('admin.sections.edit', compact('section'));
    }

    public function update(Request $request, MenuSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:menu_sections,name,' . $section->id],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $section->update($validated);

        ActivityLog::record('updated', $section, "Mengubah section: {$section->name}");

        return redirect()->route('admin.sections.index')->with('success', "Section \"{$section->name}\" berhasil diperbarui.");
    }

    public function destroy(MenuSection $section): RedirectResponse
    {
        if ($section->menus()->count() > 0) {
            return redirect()->route('admin.sections.index')->with('error', 'Section masih memiliki menu. Pindahkan atau hapus menu terlebih dahulu.');
        }

        $name = $section->name;
        $section->delete();

        ActivityLog::record('deleted', null, "Menghapus section: {$name}");

        return redirect()->route('admin.sections.index')->with('success', "Section \"{$name}\" berhasil dihapus.");
    }

    /**
     * Reorder sections via AJAX.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:menu_sections,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->input('items') as $item) {
            MenuSection::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
