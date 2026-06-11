<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Get list of active FAQs.
     */
    public function faqs()
    {
        $faqs = Faq::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }

    /**
     * Submit a new support ticket.
     */
    public function submitTicket(Request $request)
    {
        $validated = $request->validate([
            'issue_type' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'issue_type' => $validated['issue_type'],
            'message' => $validated['message'],
            'status' => 'open'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket submitted successfully. Our team will get back to you soon.',
            'data' => $ticket
        ], 201);
    }
}
