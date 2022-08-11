<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Messenger;
use App\Events\MessageEvent;
use Illuminate\Http\Request;
use App\Http\Resources\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Http\Resources\MessageBodyResource;
use Modules\PushNotification\Http\Controllers\PushNotificationController;

class MessangerController extends Controller
{
    /**
     * Messenger
     *
     * @param String $username
     * @return View
     */
    public function index($username = null)
    {
        $data['messages'] = [];
        $data['user'] = auth()->user();

        $data['selected_user'] =  User::where('username', $username)->first();

        if ($data['selected_user']) {

            if ($data['selected_user']->id == $data['user']->id) {
                flashWarning('You cannot send message your self.');
                return back();
            }
        }

        if ($data['selected_user']) {
            $unread_messages = Messenger::where('to_id', auth()->id())
                ->where('from_id', $data['selected_user']->id)
                ->where('body', '!=', '.')
                ->where('read', 0)
                ->count();

            $unread_messages ? Messenger::where('to_id', auth()->id())
                ->where('from_id', $data['selected_user']->id)
                ->where('body', '!=', '.')
                ->where('read', 0)
                ->update(['read' => 1]) : null;
        }

        $users = Messenger::join('users',  function ($join) {
            $join->on('messengers.from_id', '=', 'users.id')
                ->orOn('messengers.to_id', '=', 'users.id');
        })
            ->where(function ($q) {
                $q->where('messengers.from_id', Auth::user()->id)
                    ->orWhere('messengers.to_id', Auth::user()->id);
            })
            ->orderBy('messengers.created_at', 'desc')
            ->select('users.id as id', 'users.name', 'users.username', 'users.image', 'read')
            ->get()
            ->unique('id');

        $data['users'] = $users->where('id', '!=', Auth::user()->id)->map(function ($user) {
            $user->unread = Messenger::where('to_id', auth()->id())
                ->where('from_id', $user->id)
                ->where('body', '!=', '.')
                ->where('read', 0)
                ->count() ?? 0;
            return $user;
        });

        if ($data['selected_user']) {
            $data['messages'] = $this->getMessages($data['selected_user']);
        }

        return view('frontend.messenger.index', $data);
    }

    public function messageMarkasRead($username = null)
    {
        $data['selected_user'] =  User::where('username', $username)->first();

        if ($data['selected_user']) {
            $unread_messages = Messenger::where('to_id', auth()->id())
                ->where('from_id', $data['selected_user']->id)
                ->where('body', '!=', '.')
                ->where('read', 0)
                ->count();

            $unread_messages ? Messenger::where('to_id', auth()->id())
                ->where('from_id', $data['selected_user']->id)
                ->where('body', '!=', '.')
                ->where('read', 0)
                ->update(['read' => 1]) : null;
        }

        return ['success' => true];
    }



    /**
     * Get selected user messages
     *
     * @param App\Models\User $user
     * @return Collection
     */
    public function getMessages($user)
    {
        $id = $user->id;
        return Messenger::where(function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->where('from_id', auth()->id());
                $q->where('to_id', $id);
            })
                ->orWhere(function ($q) use ($id) {
                    $q->where('to_id', auth()->id());
                    $q->where('from_id', $id);
                });
        })
            ->where('body', '!=', '.')
            // ->latest()
            ->get();
    }



    /**
     * Send message to user
     *
     * @param Request $request
     * @param String $username
     * @return void

     */
    public function sendMessage(Request $request, $username)
    {
        $request->validate([
            'body'      =>  ['required',],
        ]);

        $user = User::where('username', $username)->firstOrFail();

        if ($user->id === auth()->id()) {
            return redirect()->route('frontend.message', $user->username);
        }

        if (!$this->checkMessageLists($user->id)) {
            $message = Messenger::create([
                'from_id'   =>  auth()->id(),
                'to_id'     =>  $user->id,
                'body'      =>  '.',
            ]);

            return redirect()->route('frontend.message', $user->username);
        }

        if ($this->checkMessageLists($user->id) && $request->body == '.') {
            return redirect()->route('frontend.message', $user->username);
        }

        $message = Messenger::create([
            'from_id'   =>  auth()->id(),
            'to_id'     =>  $user->id,
            'body'      =>  $request->body,
        ]);

        event(new MessageEvent($message->to_id, auth()->user(), new MessageBodyResource($message)));
        // if push_notification_status is enabled
        if (setting('push_notification_status')) {
            // send push notification
            $pushNotice = (new PushNotificationController)->sendNotification(
                $message->to_id,
                auth()->user()->name,
                $message->body
            );
        }

        return [
            'user' => auth()->user(),
            'message' => new MessageBodyResource($message),
        ];
    }




    /**
     * Check is already in message lists
     *
     * @param init  $id
     * @return bool
     */
    public function checkMessageLists($id)
    {
        return (bool) Messenger::where(function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->where('from_id', auth()->id());
                $q->where('to_id', $id);
            })
                ->orWhere(function ($q) use ($id) {
                    $q->where('to_id', auth()->id());
                    $q->where('from_id', $id);
                });
        })
            ->count();
    }
}
