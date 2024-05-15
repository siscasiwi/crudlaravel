<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use PhpParser\Builder\Function_;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpFoundationRedirectResponse;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title' => 'required|min:5',
            'content' => 'required|min:10'
        ]);

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //render view with post
        return view('posts.show', compact('post'));
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //delete image 
        Storage::delete('public/posts/'. $post->image);

        //delete post
        $post->delete();

        //redirect to index
        return redirect()->route('posts.index')->with(['succes' => 'Data Berhasil Dihapus!']);
    }
    public function edit(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);

        //render view with post
        return view('posts.edit', compact('post'));
    }
    public function update(Request $request, $id): RedirectResponse
{
    $this->validate($request, [
        'image' => 'image|mimes:jpeg,jpg,png|max:2048',
        'title' => 'required|min:5',
        'content' => 'required|min:10'
    ]);

    $post = Post::findOrFail($id);

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());
        Storage::delete('public/posts/'.$post->image);
        $post->update([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);
    } else {
        $post->update([
            'title' => $request->title,
            'content' => $request->content
        ]);
    }

    return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diubah!']);
}

}
