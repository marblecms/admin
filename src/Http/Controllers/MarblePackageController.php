<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Blueprint;
use Marble\Admin\Services\MarblePackageExporter;
use Marble\Admin\Services\MarblePackageImporter;

class MarblePackageController extends Controller
{
    /** Built-in field type identifiers — excluded from export UI */
    protected const BUILT_IN_FIELD_TYPES = [
        'textfield',
        'textblock',
        'htmlblock',
        'selectbox',
        'checkbox',
        'date',
        'datetime',
        'time',
        'image',
        'images',
        'object_relation',
        'object_relation_list',
        'keyvalue_store',
        'repeater',
        'file',
        'files',
    ];

    public function index()
    {
        $blueprints = Blueprint::orderBy('name')
            ->get()
            ->filter(fn ($bp) => !str_starts_with($bp->identifier, 'system_'))
            ->values();

        $allFieldTypes    = Marble::fieldTypes();
        $customFieldTypes = array_filter(
            $allFieldTypes,
            fn ($ft) => !in_array($ft->identifier(), self::BUILT_IN_FIELD_TYPES, true)
        );

        $activeTab = session('import_log') ? 'import' : 'export';

        return view('marble::package.index', compact('blueprints', 'customFieldTypes', 'activeTab'));
    }

    public function exportForm()
    {
        $blueprints = Blueprint::orderBy('name')
            ->get()
            ->filter(fn ($bp) => !str_starts_with($bp->identifier, 'system_'))
            ->values();

        $allFieldTypes  = Marble::fieldTypes();
        $customFieldTypes = array_filter(
            $allFieldTypes,
            fn ($ft) => !in_array($ft->identifier(), self::BUILT_IN_FIELD_TYPES, true)
        );

        return view('marble::package.export', compact('blueprints', 'customFieldTypes'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'package_name'   => 'nullable|string|max:100|regex:/^[a-zA-Z0-9_\-]+$/',
            'blueprint_ids'  => 'nullable|array',
            'blueprint_ids.*' => 'integer',
            'field_types'    => 'nullable|array',
            'field_types.*'  => 'string',
        ]);

        $packageName        = $request->input('package_name', 'marble-package');
        $blueprintIds       = $request->input('blueprint_ids', []);
        $fieldTypeIds       = $request->input('field_types', []);

        $exporter = new MarblePackageExporter();
        $zipPath  = $exporter->export($blueprintIds, $fieldTypeIds, $packageName);

        return response()->download($zipPath, $packageName . '.marble.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function importForm()
    {
        return view('marble::package.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'package' => 'required|file|mimes:zip',
        ]);

        $file    = $request->file('package');
        $tmpPath = $file->getRealPath();

        $importer = new MarblePackageImporter();
        $result   = $importer->import($tmpPath);

        session()->flash('import_log', $result['log']);
        session()->flash('import_success', $result['success']);

        return redirect()->route('marble.package.index');
    }
}
