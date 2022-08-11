<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Messenger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewMessageNotification;

class MessengerController extends Controller
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
        $data['user'] = auth('api')->user();

        $users = Messenger::join('users',  function ($join) {
            $join->on('messengers.from_id', '=', 'users.id')
                ->orOn('messengers.to_id', '=', 'users.id');
        })
            ->where(function ($q) {
                $q->where('messengers.from_id', Auth::user()->id)
                    ->orWhere('messengers.to_id', Auth::user()->id);
            })
            ->orderBy('messengers.created_at', 'desc')
            ->select('users.id as id', 'users.name', 'users.username', 'users.image')
            ->get()
            ->unique('id');

        $data['users'] = $users
            ->where('id', '!=', Auth::user()->id)
            ->map(function ($item) {
                $item->image_url = $item->image ? asset($item->image) : asset('backend/image/default-user.png');
                return $item;
            });


        $data['selected_user'] =  User::where('username', $username)->first();

        if ($data['selected_user']) {
            $data['messages'] = $this->getMessages($data['selected_user']);
        }

        return response()->json($data, 200);
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
            ->latest()
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
            'body' => ['required'],
        ]);

        $user = User::where('username', $username)->firstOrFail();

        if ($user->id === auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can not send message to yourself',
            ]);
        }

        if (!$this->checkMessageLists($user->id)) {
            $message = Messenger::create([
                'from_id'   =>  auth('api')->id(),
                'to_id'     =>  $user->id,
                'body'      =>  '.',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message,
            ]);
        }

        $message = Messenger::create([
            'from_id'   =>  auth('api')->id(),
            'to_id'     =>  $user->id,
            'body'      =>  $request->body,
        ]);

        if (checkSetup('mail')) {
            $user->notify(new NewMessageNotification($message, auth()->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ]);
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
