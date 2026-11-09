<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPresenceController extends Controller
{
    public function __invoke(Request $request)
    {
        DB::table('user_presence')->updateOrInsert(
            ['user_id' => $request->user()->id],
            ['last_seen_at' => now()],
        );

        return response()->noContent();
    }
}
