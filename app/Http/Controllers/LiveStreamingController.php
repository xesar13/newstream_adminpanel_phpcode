<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\LiveStreaming;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LiveStreamingController extends Controller
{
    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['live-streaming-list', 'live-streaming-create', 'live-streaming-edit', 'live-streaming-delete']);
        try {
            $languageList = Language::where('status', 1)->get();
            return view('live-streaming', compact('languageList'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        ResponseService::noPermissionThenRedirect('live-streaming-list');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $sql = LiveStreaming::with('language');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $sql = $sql->where(function ($q) use ($search) {
                $q->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhere('url', 'LIKE', "%{$search}%");
            });
        }
        if ($request->has('language_id') && $request->language_id) {
            $sql = $sql->where('language_id', $request->language_id);
        }
        $total = $sql->count('id');
        $sql = $sql->skip($offset)->take($limit)->orderBy($sort, $order);
        $rows = $sql->get()->map(function ($row) {
            $edit = '';
            if (auth()->user()->can('live-streaming-edit')) {
                $edit = '<a class="dropdown-item edit-data" data-toggle="modal" data-target="#editDataModal" title="' . __('edit') . '"><i class="fa fa-pen mr-1 text-primary"></i>' . __('edit') . '</a>';
            }
            $delete = '';
            if (auth()->user()->can('live-streaming-delete')) {
                $delete = '<a data-url="' . url('live_streaming', $row->id) . '" class="dropdown-item delete-form" data-id="' . $row->id . '" title="' . __('delete') . '"><i class="fa fa-trash mr-1 text-danger"></i>' . __('delete') . '</a>';
            }
            $operate = '';
            if ($edit == '' && $delete == '') {
                $operate = '-';
            } else {
                $operate =
                '<div class="dropdown">
                            <a href="javascript:void(0)" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <button class="btn btn-primary btn-sm px-3"><i class="fas fa-ellipsis-v"></i></button>
                            </a>
                            <div class="dropdown-menu dropdown-scrollbar" aria-labelledby="dropdownMenuButton">
                            ' .
                $edit .
                $delete .
                '
                            </div>
                        </div>';
            }

            json_decode($row->meta_keyword);
            if (json_last_error() === JSON_ERROR_NONE) {
                $meta_keyword = json_decode($row->meta_keyword);
            } else {
                $meta_keyword = $row->meta_keyword;
            }
            return [
                'id' => $row->id,
                'language_id' => $row->language_id ?? '',
                'language' => $row->language->language ?? '',
                'title' => $row->title,
                'type' => str_replace('_', ' ', $row->type),
                'type1' => $row->type,
                'url' => $row->url,
                'image' => !empty($row->image) ? '<a href="' . $row->image . '" data-toggle="lightbox" data-title="Image"><img src="' . $row->image . '" height="50" width="50" class = "images_border"></a>' : '-',
                'meta_keyword' => $meta_keyword,
                'schema_markup' => $row->schema_markup,
                'meta_title' => $row->meta_title,
                'meta_description' => $row->meta_description,
                'operate' => $operate,
            ];
        });
        return response()->json([
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        ResponseService::noPermissionThenRedirect('live-streaming-create');
        $request->validate([
            'title' => 'required',
            'language' => 'required',
            'type' => 'required',
            'file' => 'required',
            'url' => $request->type == 'url_youtube' ? 'required|youtube_url' : '',
        ]);
        $live_streaming = new LiveStreaming();
        $live_streaming->title = $request->title;
        $live_streaming->type = $request->type;
        $live_streaming->url = $request->url;
        $live_streaming->language_id = $request->language;
        $live_streaming->schema_markup = $request->schema_markup ?? '';
        $live_streaming->meta_title = $request->meta_title ?? '';
        $live_streaming->meta_description = $request->meta_description ?? '';
        $meta_keyword = json_decode($request->meta_keyword, true);
        $live_streaming->meta_keyword = $meta_keyword ? get_meta_keyword($meta_keyword) : '';
        if ($request->hasFile('file')) {
            $live_streaming->image = compressAndUpload($request->file('file'), 'liveStreaming');
        } else {
            $live_streaming->image = '';
        }
        $live_streaming->save();
        $response = [
            'error' => false,
            'message' => __('created_success'),
        ];
        return response()->json($response);
    }

    public function update(Request $request)
    {
        ResponseService::noPermissionThenRedirect('live-streaming-edit');
        $request->validate([
            'title' => 'required',
            'language' => 'required',
            'type' => 'required',
            'url' => 'required',
            'url' => $request->type == 'url_youtube' ? 'required|youtube_url' : '',
        ]);
        $live_streaming = LiveStreaming::find($request->edit_id);
        $live_streaming->language_id = $request->language;
        $live_streaming->title = $request->title;
        $live_streaming->type = $request->type;
        $live_streaming->url = $request->url;
        if ($request->hasFile('file')) {
            $live_streaming->image = compressAndReplace($request->file('file'), 'liveStreaming', $live_streaming->getRawOriginal('image'));
        }
        $live_streaming->schema_markup = $request->schema_markup ?? '';
        $live_streaming->meta_title = $request->meta_title ?? '';
        $live_streaming->meta_description = $request->meta_description ?? '';
        $meta_keyword = json_decode($request->meta_keyword, true);
        $live_streaming->meta_keyword = $meta_keyword ? get_meta_keyword($meta_keyword) : '';
        $live_streaming->save();
        $response = [
            'error' => false,
            'message' => __('updated_success'),
        ];
        return response()->json($response);
    }

    public function destroy(string $id)
    {
        ResponseService::noPermissionThenRedirect('live-streaming-delete');
        LiveStreaming::find($id)->delete();
        $response = [
            'error' => false,
            'message' => __('deleted_success'),
        ];
        return response()->json($response);
    }
}
