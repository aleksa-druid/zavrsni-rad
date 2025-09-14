<?php
// routes/channels.php
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;
use MongoDB\BSON\ObjectId;

Broadcast::routes(['middleware' => ['web','auth']]);

Broadcast::channel('chat.{roomId}', function ($user, string $roomId) {
    $rid   = preg_match('/^[0-9a-f]{24}$/i', $roomId) ? new ObjectId($roomId) : $roomId;
    $uid   = (string) $user->getAuthIdentifier();
    $uidOb = preg_match('/^[0-9a-f]{24}$/i', $uid) ? new ObjectId($uid) : null;

    $ok = ChatRoom::where('_id', $rid)
        ->where(function ($q) use ($uid, $uidOb) {
            $q->where('participant_ids', $uid);
            if ($uidOb) $q->orWhere('participant_ids', $uidOb);
        })
        ->exists();

    logger('broadcast auth', ['roomId'=>$roomId,'userId'=>$uid,'ok'=>$ok]);
    return $ok;
});
