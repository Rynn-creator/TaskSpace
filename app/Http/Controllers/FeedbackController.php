<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.feedback.index');
        }

        $feedbacks = $request->user()->feedbacks()->latest()->get();

        return view('feedback.index', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        abort_if($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $request->user()->feedbacks()->create($validated);

        return back()->with('success', 'Feedback berhasil dikirim!');
    }

    public function adminIndex(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $status = $request->query('status', 'all');
        $query = Feedback::with('user:id,name,email,profile_photo_path');

        if ($status === 'pending') {
            $query->whereNull('admin_reply');
        } elseif ($status === 'replied') {
            $query->whereNotNull('admin_reply');
        } else {
            $status = 'all';
        }

        $feedbacks = $query->latest()->paginate(12)->withQueryString();
        $feedbackCounts = [
            'all' => Feedback::count(),
            'pending' => Feedback::whereNull('admin_reply')->count(),
            'replied' => Feedback::whereNotNull('admin_reply')->count(),
        ];

        return view('feedback.admin', compact('feedbacks', 'feedbackCounts', 'status'));
    }

    public function reply(Request $request, Feedback $feedback)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'admin_reply' => 'required|string|max:5000',
        ]);

        $feedback->update([
            'admin_reply' => $validated['admin_reply'],
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan berhasil dikirim!');
    }
}
