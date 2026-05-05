<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? null;
if (!$email) {
    echo json_encode(['error' => 'usage: php scripts/merge_member_duplicates.php EMAIL']);
    exit(1);
}

$user = User::where('email', $email)->first();
if (!$user) {
    echo json_encode(['error' => 'user_not_found']);
    exit(1);
}

$members = Member::where('user_id', $user->id)->orderBy('id')->get();
if ($members->count() <= 1) {
    echo json_encode(['message' => 'no_duplicates', 'members' => $members->pluck('id')]);
    exit(0);
}

$canonical = $members->first();
$removed = [];
DB::transaction(function () use ($members, $canonical, &$removed) {
    foreach ($members->skip(1) as $duplicate) {
        $duplicateAttendanceRows = DB::table('attendance')->where('member_id', $duplicate->id)->get();
        foreach ($duplicateAttendanceRows as $attendance) {
            $conflict = DB::table('attendance')
                ->where('member_id', $canonical->id)
                ->where('schedule_id', $attendance->schedule_id)
                ->exists();

            if (!$conflict) {
                DB::table('attendance')
                    ->where('member_id', $duplicate->id)
                    ->where('schedule_id', $attendance->schedule_id)
                    ->update(['member_id' => $canonical->id]);
            } else {
                DB::table('attendance')
                    ->where('member_id', $duplicate->id)
                    ->where('schedule_id', $attendance->schedule_id)
                    ->delete();
            }
        }

        $duplicate->delete();
        $removed[] = $duplicate->id;
    }
});

echo json_encode([
    'canonical_member_id' => $canonical->id,
    'removed_member_ids' => $removed,
], JSON_PRETTY_PRINT);
