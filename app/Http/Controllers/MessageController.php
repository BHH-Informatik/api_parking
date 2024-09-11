<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MessageRequest;


// @group Messages
// API endpoints for sending messages
class MessageController extends Controller {

    /**
     * @group Messages
     * Send a message to the admins
     *
     * @bodyParam message string required The message to send. Example: This is a message
     * @bodyParam subject string required The subject of the message. Example: BUG | Request
     *
     * @response 200 { "success": true, "message": "Message Sent" }
     */
    public function messageRequest(Request $request) {

        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $params = $request->validate([
            "message" => "required|string",
            "subject" => "required|string",
        ]);

        Mail::to(env("MAIL_TO_ADDRESS"))
            ->send(new MessageRequest([
                'from' => $user,
                'subject' => $params['subject'],
                'msg' => $params['message'],
            ]));

        return response()->json([ 'success' => true ,'message' => 'Message Sent' ], 200);
    }


}
