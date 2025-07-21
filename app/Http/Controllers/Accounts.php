<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Validator;

class Accounts extends Controller
{
    public function addInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'InvoiceNumber' => 'required|string|max:50|unique:invoices,InvoiceNumber',
            'InvoiceDate' => 'required|date|before_or_equal:today',
            'TotalAmount' => 'required|numeric|min:0',
            'Discount' => 'nullable|numeric|min:0',
            'Tax' => 'nullable|numeric|min:0',
            'FinalAmount' => 'required|numeric|min:0',
            'ClientId' => 'required|exists:clients,id',
            'BookingId' => 'nullable|exists:bookings,id',
            'Description' => 'nullable|string|max:255',
            'CreatedBy' => 'required|exists:serviceemployees,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'reason' => '', 'error' => $validator->errors()];
        }
        $total = $request->TotalAmount;
        $discount = $request->Discount ?? 0;
        $tax = $request->Tax ?? 0;
        $expectedFinal = $total - $discount + $tax;
        if (round($expectedFinal, 2) != round($request->expectedFinal, 2)) {
            return ['result' => 'failed', 'invoice' => '', 'error' => 'The final value does not match the total after deduction and tax.'];
        }

        $invoice = Invoice::create([
            'InvoiceNumber' => $request->InvoiceNumber,
            'InvoiceDate' => $request->InvoiceDate,
            'TotalAmount' => $total,
            'Discount' => $discount,
            'Tax' => $tax,
            'FinalAmount' => $request->FinalAmount,
            'ClientId' => $request->ClientId,
            'BookingId' => $request->BookingId,
            'Description' => $request->Description,
            'CreatedBy' => $request->CreatedBy,
        ]);
        return ['result' => 'success', 'invoice' => $invoice, 'error' => ''];
    }

    public function getInvoicesByDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'id' => 'nullable|numeric|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'invoices' => [], 'error' => $validator->errors()];
        }

        $query = Invoice::whereDate('InvoiceDate', '>=', $request->from)->whereDate('InvoiceDate', '<=', $request->to);

        if ($request->has('id')) {
            $query->where('ClientId', $request->id);
        }

        $invoices = $query->get();

        return ['result' => 'success', 'invoices' => $invoices, 'error' => ''];
    }

    public function addReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ReceiptNumber' => 'required|string|max:50|unique:receipts,ReceiptNumber',
            'ReceiptDate' => 'required|date',
            'Amount' => 'required|numeric|min:0.01',
            'EmployeeId' => 'required|exists:serviceemployees,id',
            'ClientId' => 'nullable|exists:clients,id',
            'BookingId' => 'nullable|exists:bookings,id',
            'Description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'receipt' => '', 'error' => $validator->errors()];
        }
        if ($request->filled('BookingId') && !$request->filled('ClientId')) {
            return ['result' => 'failed', 'receipt' => '', 'error' => 'لا يمكن ربط الحجز بدون تحديد العميل.'];
        }

        if ($request->filled('BookingId') && $request->filled('ClientId')) {
            $booking = Booking::find($request->BookingId);
            if ($booking && $booking->ClientId != $request->ClientId) {
                return ['result' => 'failed', 'receipt' => '', 'error' => 'العميل المحدد لا يتطابق مع العميل المرتبط بالحجز.'];
            }
        }

        $receipt = Receipt::create([
            'ReceiptNumber' => $request->ReceiptNumber,
            'ReceiptDate' => $request->ReceiptDate,
            'Amount' => $request->Amount,
            'EmployeeId' => $request->EmployeeId,
            'Description' => $request->Description,
            'ClientId' => $request->ClientId,
            'BookingId' => $request->BookingId,
        ]);

        return ['result' => 'success', 'receipt' => $receipt, 'error' => ''];
    }

    public function getReceipts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'receipts' => '', 'error' => $validator->errors()];
        }

        $query = Receipt::with(['client', 'employee', 'booking']);

        if ($request->filled('ReceiptNumber')) {
            $query->where('ReceiptNumber', 'like', '%' . $request->ReceiptNumber . '%');
        }

        if ($request->filled('ClientName')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->ClientName . '%');
            });
        }

        if ($request->filled('BookingId')) {
            $query->where('BookingId', $request->BookingId);
        }

        if ($request->filled('from')) {
            $query->whereDate('ReceiptDate', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('ReceiptDate', '<=', $request->to);
        }

        $receipts = $query->orderBy('ReceiptDate', 'desc')->get();

        return ['result' => 'success', 'receipts' => $receipts, 'error' => ''];
    }

    public function addAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClientId' => 'required|exists:clients,id',
            'type' => 'required|in:1,2', // 1=> Invoice 2=>Receipt
            'Number' => 'required|integer',
            'Amount' => 'required|numeric|min:0.01',
            'Date' => 'required|date',
            'Time' => 'required|date_format:H:i',
            'UserId' => 'required|exists:serviceemployees,id',
        ]);

        if ($validator->fails()) {
            return ['result' => 'failed', 'error' => $validator->errors()];
        }

        // تحقق من وجود الرقم في الجدول المناسب حسب النوع
        if ($request->type == 1) {
            $exists = Invoice::where('InvoiceNumber', $request->Number)->where('ClientId', $request->ClientId)->exists();
            if (!$exists) {
                return ['result' => 'failed', 'error' => 'Invoice not found for this client'];
            }
        } elseif ($request->type == 2) {
            $exists = Receipt::where('ReceiptNumber', $request->Number)->where('ClientId', $request->ClientId)->exists();
            if (!$exists) {
                return ['result' => 'failed', 'error' => 'Receipt not found for this client'];
            }
        }

        $account = Account::create($request->all());

        return ['result' => 'success', 'account' => $account];
    }
}
