<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Models\companyinfo;
use App\Models\invoice;
use App\Models\invoice_item;
use App\Models\payment_invoice_received;
use App\Models\payment_invoice_sent;
use App\Models\product;
use App\Models\received_invoice;
use App\Models\received_invoice_item;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Dompdf\Adapter\PDFLib;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class invoice_contr extends Controller
{
    function invoice_form(){
       /*
        $latestClientsData=Client::select('user_id', DB::raw('MAX(created_at) as latest_created_at'))
                    ->groupby('user_id')->get();
    
        $userIds = $latestClientsData->pluck('user_id');
        $latestCreatedAts = $latestClientsData->pluck('latest_created_at');

        $client = Client::whereIn('user_id', $userIds)
                       ->whereIn('created_at', $latestCreatedAts)
                       ->get();
*/

        $client=client::wherenull('state')->get();
        $products=product::all();
        $companyinfo=companyinfo::all();
        return view('invoice_form',compact('client','companyinfo','products'));
    }




    function save_invoice_admin(Request $request){
        $request->validate([
            'date' => 'required|date',
       
            'client_id' => 'required|exists:clients,id',
        
            'company_id' => 'required|exists:companyinfos,id',
        ]);
        $quantities = $request->input('quantities');
        $unit_prices = $request->input('unit_prices');
        $tvas = $request->input('tvas');
        $descriptions = $request->input('descriptions');

        if(isset($descriptions)&& isset($quantities )&& isset( $unit_prices )&&isset($tvas )){

            $invoice = new invoice();
            $invoice->date = $request->date;
      
            $invoice->client_id = $request->client_id;

            $invoice->companyinfo_id = $request->company_id;
       
            $invoice->save();

            $total_net = 0;
            $total_tva = 0;
            foreach ($descriptions as $key => $description) {
                $quantity = $quantities[$key];
                $unit_price = $unit_prices[$key];
                $tva = $tvas[$key];

                $item_total = $quantity * $unit_price;
                $item_tva = ($tva * $item_total) / 100;

                $total_net += $item_total;
                $total_tva += $item_tva;
 
                $item = new invoice_item();
                $item->description = $description;
                $item->quantity = $quantities[$key];
                $item->unit_price = $unit_prices[$key];
                $item->tva = $tvas[$key];
              
                $item->invoice_id = $invoice->id;
                $item->save();
            }
            $ttc = $total_net + $total_tva;

            $invoice->ttc = $ttc;
            $invoice->save();


            return redirect()->back()->with('success', 'Invoice created successfully');


        }else return redirect()->back()->with('error', 'ereur');
     
    }


    function invoice_form_client(){
       
        $companyinfo=companyinfo::all();
        return view('invoice_form_client',compact('companyinfo'));
    }



    public function saveInvoiceClient(Request $request){
      
        $request->validate([
            'date' => 'required|date',
  
            'invoice_number'=>'required',
           
            'company_id' => 'required|exists:companyinfos,id',
        ]);
        $descriptions = $request->input('descriptions');
        $quantities = $request->input('quantities');
        $unit_prices = $request->input('unit_prices');
        $tvas = $request->input('tvas');

        if(isset($descriptions)&& isset($quantities )&& isset( $unit_prices )&&isset($tvas )){

                        $user_id = Auth::user()->id;
                        $client = client::where('user_id', $user_id)->wherenull('state')->first();
                        if (!$client) {
                            return redirect()->back()->with('error', 'Client not found.');
                        }
                        $invoice = new received_invoice();
                        $invoice->date = $request->date;

         
                        $invoice->companyinfo_id = $request->company_id;
                        $invoice->client_id = $client->id; 
                    
                        $invoice->invoice_number = $request->invoice_number;
                    
                        $invoice->save();

                        $total_net = 0;
                        $total_tva = 0;

                        foreach ($descriptions as $key => $description) {
                            $quantity = $quantities[$key];
                            $unit_price = $unit_prices[$key];
                            $tva = $tvas[$key];

                            $item_total = $quantity * $unit_price;
                            $item_tva = ($tva * $item_total) / 100;

                            $total_net += $item_total;
                            $total_tva += $item_tva;
                          
                            $item = new received_invoice_item();
                            $item->description = $description;
                            $item->quantity = $quantities[$key];
                            $item->unit_price = $unit_prices[$key];
                            $item->tva = $tvas[$key];
                        
                            $item->received_invoice_id = $invoice->id;
                            $item->save();
                        }   
                        $ttc = $total_net + $total_tva;

                        $invoice->ttc = $ttc;
                        $invoice->save();

                        return redirect()->back()->with('success', 'Invoice created successfully.');}

                else return redirect()->back()->with('error', 'ereur');
}



function detail_invoice(Request $request,$type,$id)
{   
    
    if($type=='sent'){
        $invoice=received_invoice::findOrfail($id);
        $invoice_item = received_invoice_item::where('received_invoice_id', $invoice->id)->get();
    }
    else
    {

        $invoice=invoice::findOrfail($id);
        $invoice_item = invoice_item::where('invoice_id', $invoice->id)->get();
    }


   

    $client = client::where('id', $invoice->client_id)->first();
    $company = companyinfo::where('id', $invoice->companyinfo_id)->first();
  

    $totals = [];
    foreach ($invoice_item as $item) {
        $total = $item->quantity * $item->unit_price;
        $totals[] = $total;
    }

    $tva_array = [];
    foreach ($invoice_item as $item) {
        $tva = $item->tva * $item->quantity * $item->unit_price / 100;
        $tva_array[] = $tva;
    }

    $total_tva = array_sum($tva_array);
    $total_net = array_sum($totals);
    $ttc = $total_net + $total_tva;

    // Génération du PDF
    $pdf =FacadePdf::loadView('detail_invoice', [
        'invoice' => $invoice,
        'client' => $client,
        'invoice_item' => $invoice_item,
        'company' => $company,
        'totals' => $totals,
        'tva_array' => $tva_array,
        'total_net' => $total_net,
        'total_tva' => $total_tva,
        'ttc' => $ttc,
        'type'=>$type
    ]);

    // Retourne le PDF en tant que téléchargement
    return $pdf->stream('invoice_' . $invoice->id . '.pdf');
}

function payment_form(Request $request){
    $type=$request->type;
    $id=$request->id;
    if($type==='sent'){
        $invoice = received_invoice::find($id);
        $payment_detail = payment_invoice_received::where('received_invoice_id', $id)
        ->orderBy('created_at', 'desc') 
        ->get();
    $rest=$invoice->ttc-$invoice->paymentamount;


    }
    else{   $invoice = Invoice::find($id);
            $payment_detail=payment_invoice_sent::where('invoice_id',$id)
            ->orderBy('created_at', 'desc') 
            ->get();
            $rest=$invoice->ttc-$invoice->paymentamount;     

    }

    return view('payment_form',compact('invoice','payment_detail','type','id','rest'));
}


function payment_form_save(Request $request){

    if($request->type==='sent'){
        $invoice = received_invoice::find($request->id);
    
        if ($invoice) { 
            payment_invoice_received::create([
             'received_invoice_id'=>$request->id,
             'paye'=>$request->payment,
             'payment_date'=>$request->date,]);

             $paye=payment_invoice_received::where('received_invoice_id',$request->id)->sum('paye');
             $invoice->paymentamount=$paye;
             if($invoice->ttc===$invoice->paymentamount) $invoice->status='paid';
             if($invoice->ttc > $invoice->paymentamount) $invoice->status='partially paid';


             $invoice->save();

          
   
             
         
         
         } else {
         
            return back()->with('error', 'error');


         }

    }
    else{
                     $invoice = Invoice::find($request->id);
                        
                    if ($invoice) { 
                       payment_invoice_sent::create([
                        'invoice_id'=>$request->id,
                        'paye'=>$request->payment,
                        'payment_date'=>$request->date,]);

                        $paye=payment_invoice_sent::where('invoice_id',$request->id)->sum('paye');
                        $invoice->paymentamount=$paye;
                        if($invoice->ttc===$invoice->paymentamount) $invoice->status='paid';
                        if($invoice->ttc > $invoice->paymentamount) $invoice->status='partially paid';


                        $invoice->save();

                     
              
                        
                    
                    
                    } else {
                    
                        return back()->with('error', 'error');


                    }

    }
    return to_route('payment_form', ['id' => $request->id, 'type' => $request->type])
    ->with('success', 'Payment information saved successfully!');
}



function payment_detail(Request $request){
    $type=$request->type;
    $id=$request->id;
    if($type==='sent'){
        $invoice = received_invoice::find($id);
        $payment_detail = payment_invoice_received::where('received_invoice_id', $id)
        ->orderBy('created_at', 'desc') 
        ->get();
    $rest=$invoice->ttc-$invoice->paymentamount;


    }
    else{   $invoice = Invoice::find($id);
            $payment_detail=payment_invoice_sent::where('invoice_id',$id)
            ->orderBy('created_at', 'desc') 
            ->get();
            $rest=$invoice->ttc-$invoice->paymentamount;     

    }

    return view('payment_detail',compact('invoice','payment_detail','type','id','rest'));
}


}
