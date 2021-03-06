<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use PDF;
use Log;
use Mail;

use App\Receipt;
use App\Notifications\ReceiptForward;

class ReceiptsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Receipt'
        ]);
    }

    public function show($id)
    {
        $receipt = Receipt::findOrFail($id);

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas)) {
            return view('receipt.edit', ['receipt' => $receipt]);
        }
        else if ($user->can('movements.view', $user->gas)) {
            return view('receipt.show', ['receipt' => $receipt]);
        }
        else {
            abort(503);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $receipt = Receipt::findOrFail($id);
        $receipt->date = decodeDate($request->input('date'));
        $receipt->save();

        return $this->successResponse([
            'id' => $receipt->id,
            'header' => $receipt->printableHeader(),
            'url' => url('receipts/' . $receipt->id),
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        Receipt::findOrFail($id)->delete();

        return $this->successResponse();
    }

    public function download(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);
        $user = Auth::user();

        if ($user->can('movements.admin', $user->gas) || $user->can('movements.view', $user->gas) || $receipt->user_id == $user->id) {
            $html = view('documents.receipt', ['receipt' => $receipt])->render();
            $title = _i('Fattura %s', [$receipt->number]);
            $filename = sanitizeFilename($title . '.pdf');
            PDF::SetTitle($title);
            PDF::AddPage();
            PDF::writeHTML($html, true, false, true, false, '');

            $send_mail = $request->has('send_mail');
            if ($send_mail) {
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);
                PDF::Output($temp_file_path, 'F');

                $receipt->user->notify(new ReceiptForward($temp_file_path));
                $receipt->mailed = true;

                @unlink($temp_file_path);
                $receipt->save();
            }
            else {
                PDF::Output($filename, 'D');
            }
        }
        else {
            abort(503);
        }
    }
}
