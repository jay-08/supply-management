<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\SupplyRequest;
use App\Models\PurchaseOrder;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $message = trim(strtolower($request->input('message', '')));

        if (empty($message)) {
            return response()->json([
                'reply' => 'Hello! I am your Supply Assistant. How can I help you today? You can ask me how to request supplies, track a request, or check PO workflows.'
            ]);
        }

        // Fuzzy Keyword Matching Engine
        $reply = $this->getAnswer($message);

        return response()->json([
            'reply' => $reply
        ]);
    }

    private function getAnswer(string $msg): string
    {
        // 1. Requesting Supplies
        if (str_contains($msg, 'request') && (str_contains($msg, 'how') || str_contains($msg, 'new') || str_contains($msg, 'create') || str_contains($msg, 'submit') || str_contains($msg, 'cart') || str_contains($msg, 'buy'))) {
            return "📦 **How to Request Supplies:**\n1. Go to the **Supply Catalog** on the top menu.\n2. Browse items and click **Add to Request**.\n3. Open your **Request Cart** and click **Review & Confirm**.\n4. Select your Department, enter the Purpose, and click **Submit Request**.\n5. Save your unique tracking number (e.g., REQ-202607-0001) to monitor status!";
        }

        // 2. Tracking Requests or POs
        if (str_contains($msg, 'track') || str_contains($msg, 'status') || str_contains($msg, 'where is') || str_contains($msg, 'check')) {
            return "🔍 **How to Track Status:**\n- Go to **Track Status** in the top navigation bar.\n- Enter your Request Number (e.g. `REQ-202607-0001`) or Purchase Order Number (e.g. `PO-202607-0001`).\n- Click **Track Status** to view real-time approval progress and status updates!";
        }

        // 3. Claiming Supplies
        if (str_contains($msg, 'claim') || str_contains($msg, 'pick up') || str_contains($msg, 'pickup') || str_contains($msg, 'receive item')) {
            return "✅ **How to Claim Issued Supplies:**\nWhen your request status reaches **Issued**:\n1. Open your Request details page or track your request.\n2. Click the green **Claim Supplies** button.\n3. The status will update to **Claimed**!";
        }

        // 4. Purchase Order Workflow
        if (str_contains($msg, 'po') || str_contains($msg, 'purchase order') || str_contains($msg, 'workflow') || str_contains($msg, 'routing') || str_contains($msg, 'budget') || str_contains($msg, 'accounting') || str_contains($msg, 'rd') || str_contains($msg, 'ard')) {
            return "⏱️ **Purchase Order (PO) Workflow:**\n1. **Draft / Pending**: Created by Supply Officer.\n2. **Budget Officer**: Reviews and approves budget allocation.\n3. **Accounting**: Verifies financial documents.\n4. **ARD / RD**: Executive approval by Regional Director.\n5. **Sent to Supplier**: PO sent for fulfillment.\n6. **Delivered**: Goods received via GRN.";
        }

        // 5. Staff Accounts & Roles
        if (str_contains($msg, 'staff') || str_contains($msg, 'role') || str_contains($msg, 'permission') || str_contains($msg, 'officer') || str_contains($msg, 'account')) {
            return "👥 **Officer Staff Accounts:**\neach office (Supply, Budget, Accounting, ARD, RD) has designated **Staff Accounts** (`supply-staff`, `budget-staff`, `accounting-staff`, `ard-staff`, `rd-staff`). Staff accounts can view records and **Receive POs** routed to their department.";
        }

        // 6. Stock & Inventory
        if (str_contains($msg, 'stock') || str_contains($msg, 'inventory') || str_contains($msg, 'available') || str_contains($msg, 'item') || str_contains($msg, 'out of stock')) {
            $totalItems = InventoryItem::where('status', 'active')->count();
            return "📊 **Inventory System Status:**\nCurrently tracking **{$totalItems} active supply items** in stock. Items below minimum threshold automatically alert Supply Officers for replenishment.";
        }

        // 7. Login & Access
        if (str_contains($msg, 'login') || str_contains($msg, 'sign in') || str_contains($msg, 'password') || str_contains($msg, 'access')) {
            return "🔐 **System Access & Login:**\n- Staff & Officers can log in via **Staff Login** at the top right.\n- Public users & office personnel do NOT need an account to browse supplies and submit requests!";
        }

        // 8. Default AI Assistant Response
        return "🤖 **Supply MS Assistant:**\nI can help you with:\n- 📦 How to request supplies\n- 🔍 Tracking your Request or PO status\n- ⏱️ Understanding PO routing & approvals\n- 👥 Staff account access & roles\n\nFeel free to type any question or click a suggested topic below!";
    }
}
