<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminPasswordResetRequestController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $requests = PasswordResetRequest::with(['user', 'processedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.password-reset-requests', compact('requests'));
    }

    public function sendResetLink(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($passwordResetRequest->status === 'pending', 404);
        abort_unless(
            $passwordResetRequest->user && ! $passwordResetRequest->user->isAdmin(),
            404,
        );

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return back()->withErrors([
                'email' => 'Pengiriman email belum dikonfigurasi. Atur MAIL_MAILER dan kredensial SMTP di .env terlebih dahulu.',
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $passwordResetRequest->user->email]);
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Password reset email delivery failed.', [
                'password_reset_request_id' => $passwordResetRequest->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors([
                'email' => 'Email reset gagal dikirim karena server email menolak koneksi atau autentikasi. Periksa konfigurasi SMTP dan Gmail App Password di .env, lalu coba lagi.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => __($status)]);
        }

        $passwordResetRequest->update([
            'status' => 'sent',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Link reset password telah dikirim ke email pengguna.');
    }
}
