<?php

namespace App\Http\Controllers;

use App\Http\Requests\admin_update_request;
use App\Http\Requests\update_request;
use App\Models\client;
use App\Models\companyinfo;
use App\Models\devis;
use App\Models\devis_recu;
use App\Models\invoice;
use App\Models\received_invoice;
use App\Models\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class admin_contr extends Controller
{
    function admin_list_acc(){
        if (Auth::check() && Auth::user()->usertype === 'admin') {

            $users=User::whereNotIn('usertype',['admin'])->get(); 
        
            return view('admin_list_acc',compact('users'));
        }

      
        return to_route('home')->with('error', 'Accès non autorisé');

        
       
    }


    function account_update(User $user){
        return view('admin_update',compact('user'));

    }


    function do_update(admin_update_request $request,User $user){
 
        $user->name=$request->input('name');
        $user->email=$request->input('email');

        if(!empty($request->input('password'))){

            $user->password= Hash::make($request->input('password'))  ;
        }
        $user->save();
        return to_route('admin_list_acc');


    }


    function delete_account(User $user){
        $user->delete();

        return to_route('admin_list_acc');

    }


    function validation_list(){
       
        if (Auth::check() && Auth::user()->usertype === 'admin') {

            $users=User::whereNotIn('usertype',['admin'])->where('uservalid','nv')->get(); 
        
            return view('validation_list',compact('users'));
        }

      
        return to_route('home')->with('error', 'Accès non autorisé');

    }


    function validation_acc(user $user){
        if (Auth::check() && Auth::user()->usertype === 'admin') {

         $user->uservalid='v';
         $user->save();
         return to_route('validation_list');
         
        }

      
        return to_route('home')->with('error', 'Accès non autorisé');

        
    }


    public function validation_Change(Request $request, User $user)
    {
       
        $user->uservalid = $user->uservalid === 'v' ? 'nv' : 'v';
        $user->save();
    
       
        return response()->json(['validation' => $user->uservalid]);
    }
    

    function search_users_email(Request $request){
        $email = $request->input('email');
        $users = User::where('email', 'LIKE', "%$email%")->whereNotIn('usertype',['admin'])->get();
        return view('admin_list_acc', compact('users'));
    }


    function client_information(Request $request,$id){
      
        $client = Client::where('user_id', $id)
        ->whereNull('state') 
        ->first();

        $unassignedClients = client::whereNull('state') ->whereNull('user_id')->get();
        if ($client) {
            return view('admin_client',compact('id','client','unassignedClients'));

                     }
         else
            {   
                
                return view('admin_client',compact('id','unassignedClients'));}
        
    }



    function add_update_client(Request $request,$id){

   $client = Client::where('user_id', $id)->whereNull('state')->first();
   if ($client) {
    $client->state = 'deleted';
    $client->save();

   }


 /*  
        if ($client) {
          
            $client->name = $request->name;
            $client->address = $request->address;
            $client->tel = $request->tel;
            $client->user_id = $request->id;
            $client->save();
        
            return to_route('admin_list_acc')->with('success', 'Client mis à jour avec succès.');
        } else {
           
           
        
            return to_route('admin_list_acc')->with('success', 'Client ajouté avec succès.');
        }*/
        Client::create([
            'name' => $request->name,
            'address' => $request->address,
            'tel' => $request->tel,
            'user_id' => $request->id,
        ]);
        return to_route('admin_list_acc')->with('success', 'Client ajouté avec succès.');
    }


    function company_info(){
        if (Auth::check() && Auth::user()->usertype === 'admin') {
            $companyInfo=companyinfo::all();
            return view('company_info_form',compact('companyInfo'));   
        }

      
        return to_route('home')->with('error', 'Accès non autorisé');

    }


    function company_info_save(Request $request){

        $companyInfo = new companyinfo();
        $companyInfo->name = $request->name;
        $companyInfo->address = $request->address;
        $companyInfo->city = $request->city;     
        $companyInfo->tel = $request->tel;
        $companyInfo->email = $request->email;
      
        $companyInfo->save();

        return redirect()->back()->with('success', 'Company information saved successfully.');
    
    }




    function list_client_invoice(Request $request,$id){
       return view('list_client_invoice',compact('id'));

    }


    public function sort_client_invoice(Request $request)
    {
        
        $invoices = [];
      
    
            $id=$request->input('id');
        
            $filter = $request->input('filter');    
            
   
                if ($filter === 'sent') {
                    $invoices = invoice::select('*', DB::raw("'received' as type"))
                                       ->where('client_id', $id)
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                                       
                } 
                elseif($filter === 'received') 
                {
                    $invoices = received_invoice::select('*', DB::raw("'sent' as type"))
                                       ->where('client_id', $id)                                     
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                }
                else{
                    $invoice = invoice::select('id', 'date','created_at','payment_date', 'due_date','status',DB::raw("null as invoice_number"), DB::raw("'received' as type"))
                    ->where('client_id', $id);
                
                    $received_invoice = received_invoice::select('id', 'date','created_at','payment_date', 'due_date','status','invoice_number', DB::raw("'sent' as type"))
                    ->where('client_id', $id);
                  

                    $invoices=$invoice->union($received_invoice) 
                                ->orderBy('created_at', 'desc')
                                ->get();

                

            }


        
        return view('sort_invoice',compact('invoices')); 
               
    }
    

    
public function search_client_name(Request $request)
{
    $query = $request->input('query');

    $idclientinvoice = DB::table('invoices')->pluck('client_id');
    $receivedInvoiceClientId = DB::table('received_invoices')->pluck('client_id');
    $idclientdevis = DB::table('devis')->pluck('client_id');
    $idclientdevisrecus = DB::table('devis_recus')->pluck('client_id');

    $allClientIds = $idclientinvoice->merge($receivedInvoiceClientId)
    ->merge($idclientdevis)
    ->merge($idclientdevisrecus)
    ->unique();


    $clients = Client::whereIn('id', $allClientIds)->where('name', 'like', "%{$query}%")->get();
    if ($clients->isEmpty()) {
        return response()->json(['message' => 'No clients found.']);
    }
    return response()->json(['clients' => $clients]);
}


function list_client_devis(Request $request,$id){
    return view('list_client_devis',compact('id'));

 }


 public function sort_client_devis(Request $request)
    {
        
        $devis = [];
      
    
            $id=$request->input('id');
        
            $filter = $request->input('filter');    
            
   
                if ($filter === 'sent') {
                    $devis = devis::select('*', DB::raw("'received' as type"))
                                       ->where('client_id', $id)
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                                       
                } 
                elseif($filter === 'received') 
                {
                    $devis = devis_recu::select('*', DB::raw("'sent' as type"))
                                       ->where('client_id', $id)                                     
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                }
                else{
                    $devis = devis::select('id', 'date','created_at',DB::raw("null as devis_number"), DB::raw("'received' as type"))
                    ->where('client_id', $id);
                
                    $received_devis = devis_recu::select('id', 'date','created_at', 'devis_number', DB::raw("'sent' as type"))
                    ->where('client_id', $id);
                  

                    $devis=$devis->union($received_devis) 
                                ->orderBy('created_at', 'desc')
                                ->get();

                

            }


        
        return view('sort_devis',compact('devis')); 
               
    }
    

                                                              

 public function addClientAjax(Request $request)
    {
        $client = Client::create([
            'name' => $request->name,
            'address' => $request->address,
            'tel' => $request->tel,
            'user_id' => null,
        ]);
       
        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'address' =>$client->address,
        ]);
    }

    function link_user_client(Request $request,$id){
       $id_client= $request->unassigned_client_id;
       if(empty($id_client)){   return redirect()->back(); }
       else{
            $previousClient = Client::where('user_id', $id)->whereNull('state')->first();
                if ($previousClient) {
                    $previousClient->state = 'deleted';
                    $previousClient->save();

                }

            $client = Client::find($id_client);
            $client->user_id=$id;
            $client->save();
            return redirect()->back();
       }    
   
    }




    public function dashboard_admin()
    {
       $invoice_count=$this->invoice_count();
       $ttc_chart=$this->ttc_chart();
       return view('dashboard', compact('invoice_count','ttc_chart'));
    
}
/*
public function invoice_count()
{
    $invoices = $this->trimestre_data_currentYear('invoices');
    $labels = [];
    $values = [];

    $invoiceCount = $invoices->groupBy(['quarter']);

foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
    $count = isset($invoiceCount[$quarter]) ? $invoiceCount[$quarter]->count() : 0;
    $labels[] = $quarter;  
    $values[] = $count;   
}


$chart = (new LarapexChart)->barChart()
    ->setTitle('Nombre de Factures Envoyées par Trimestre ')
    ->setSubtitle('Répartition trimestrielle des factures')
    ->setXAxis($labels)
    ->setDataset([
        [
            'name' => 'Nombre de Factures',
            'data' => $values
        ]
    ]);

return $chart;
}
*/

public function invoice_count()
{
    // Fetch data separately for each table
    $draftInvoices = $this->trimestre_data_currentYear('invoices');
    $receivedInvoices = $this->trimestre_data_currentYear('received_invoices');

    $labels = [];
    $draftValues = [];
    $receivedValues = [];

    // Group data by quarter
    $draftGrouped = $draftInvoices->groupBy('quarter');
    $receivedGrouped = $receivedInvoices->groupBy('quarter');

    foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
        $draftCount = isset($draftGrouped[$quarter]) ? $draftGrouped[$quarter]->count() : 0;
        $receivedCount = isset($receivedGrouped[$quarter]) ? $receivedGrouped[$quarter]->count() : 0;

        $labels[] = $quarter;
        $draftValues[] = $draftCount;
        $receivedValues[] = $receivedCount;
    }

    $chart = (new LarapexChart)->barChart()
        ->setTitle('Nombre de Factures par Trimestre')
        ->setSubtitle('Répartition trimestrielle des factures rédigées et reçues')
        ->setXAxis($labels)
        ->setDataset([
            [
                'name' => 'Factures Rédigées',
                'data' => $draftValues,
                'color' => '#1f77b4' // Couleur pour les factures rédigées
            ],
            [
                'name' => 'Factures Reçues',
                'data' => $receivedValues,
                'color' => '#ff7f0e' // Couleur pour les factures reçues
            ]
        ]);

    return $chart;
}


/*
public function ttc_chart()
{
    $items = $this->trimestre_data_currentYear('invoice_items');
    $labels = [];
    $values = [];

   
    $itemsGrouped = $items->groupBy('quarter');

  
    foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
        $itemsInQuarter = isset($itemsGrouped[$quarter]) ? $itemsGrouped[$quarter] : collect();

        $ttc = 0;


        foreach ($itemsInQuarter as $item) {
            $totalPrice = $item->quantity * $item->unit_price;
            $totalTva = $totalPrice * ($item->tva / 100);
            $ttc += $totalPrice + $totalTva;  
        }
    
        $labels[] = $quarter;  
        $values[] = $ttc;      
    }

    
    $chart = (new LarapexChart)->barChart()
        ->setTitle('Total TTC par Trimestre pour Factures Rédigées')
        ->setSubtitle('Répartition trimestrielle du TTC')
        ->setXAxis($labels)
        ->setDataset([
            [
                'name' => 'Total TTC',
                'data' => $values
            ]
        ]);

    return $chart;
}

*/
public function ttc_chart()
{
    // Fetch data separately for each table
    $draftItems = $this->trimestre_data_currentYear('invoice_items');
    $receivedItems = $this->trimestre_data_currentYear('received_invoice_items');

    $labels = [];
    $draftValues = [];
    $receivedValues = [];

    // Group data by quarter
    $draftGrouped = $draftItems->groupBy('quarter');
    $receivedGrouped = $receivedItems->groupBy('quarter');

    foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
        // Calculate TTC for draft items
        $draftItemsInQuarter = isset($draftGrouped[$quarter]) ? $draftGrouped[$quarter] : collect();
        $draftTtc = $draftItemsInQuarter->reduce(function ($carry, $item) {
            $totalPrice = $item->quantity * $item->unit_price;
            $totalTva = $totalPrice * ($item->tva / 100);
            return $carry + ($totalPrice + $totalTva);
        }, 0);

        // Calculate TTC for received items
        $receivedItemsInQuarter = isset($receivedGrouped[$quarter]) ? $receivedGrouped[$quarter] : collect();
        $receivedTtc = $receivedItemsInQuarter->reduce(function ($carry, $item) {
            $totalPrice = $item->quantity * $item->unit_price;
            $totalTva = $totalPrice * ($item->tva / 100);
            return $carry + ($totalPrice + $totalTva);
        }, 0);

        $labels[] = $quarter;
        $draftValues[] = $draftTtc;
        $receivedValues[] = $receivedTtc;
    }

    $chart = (new LarapexChart)->barChart()
        ->setTitle('Total TTC par Trimestre')
        ->setSubtitle('Répartition trimestrielle du TTC des factures rédigées et reçues')
        ->setXAxis($labels)
        ->setDataset([
            [
                'name' => 'TTC Factures Rédigées',
                'data' => $draftValues,
                'color' => '#1f77b4' // Couleur pour les factures rédigées
            ],
            [
                'name' => 'TTC Factures Reçues',
                'data' => $receivedValues,
                'color' => '#ff7f0e' // Couleur pour les factures reçues
            ]
        ]);

    return $chart;
}

public function trimestre_data_currentYear($table)
{
    $currentYear = date('Y');
    $query = DB::table($table)
        ->select(DB::raw(
            "strftime('%Y', created_at) as year,
            CASE
                WHEN strftime('%m', created_at) IN ('01', '02', '03') THEN 'Q1'
                WHEN strftime('%m', created_at) IN ('04', '05', '06') THEN 'Q2'
                WHEN strftime('%m', created_at) IN ('07', '08', '09') THEN 'Q3'
                WHEN strftime('%m', created_at) IN ('10', '11', '12') THEN 'Q4'
            END as quarter,
            *
            "
        ))
        ->orderBy('year')
        ->orderBy('quarter');
        
 
        $query->whereYear('created_at', $currentYear );
   
    
    return $query->get();
}


}



