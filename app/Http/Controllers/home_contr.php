<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Models\devis;
use App\Models\devis_recu;
use App\Models\invoice;
use App\Models\received_invoice;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use ArielMejiaDev\LarapexCharts\LarapexChart;
class home_contr extends Controller
{
  public function home(Request $request)
    {
        $user = Auth::user();
      

        if ($user->usertype === 'user') {
            
            return view('home', compact( 'user'));
                          
            
        } else {
            $idclientinvoice = DB::table('invoices')->pluck('client_id');
            $receivedInvoiceClientId = DB::table('received_invoices')->pluck('client_id');
            $idclientdevis = DB::table('devis')->pluck('client_id');
            $idclientdevisrecus = DB::table('devis_recus')->pluck('client_id');

            $allClientIds = $idclientinvoice->merge($receivedInvoiceClientId)
            ->merge($idclientdevis)
            ->merge($idclientdevisrecus)
            ->unique();
            $clients = Client::whereIn('id', $allClientIds)
            ->orderBy('created_at', 'desc')
            ->get();


            return view('home', compact( 'clients', 'user'));
        }

       
    }



    public function sort_invoice(Request $request)
    {
        $user = Auth::user();
        $invoices = [];
    
    
        
            $client = client::where('user_id', $user->id)->wherenull('state')->first();
            $filter = $request->input('filter');    
          
            if ($client) {
                if ($filter === 'received') {
                    $invoices = invoice::select('*', DB::raw("'received' as type"))
                                       ->where('client_id', $client->id)
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                             
                } 
                elseif($filter === 'sent') 
                {
                    $invoices = received_invoice::select('*', DB::raw("'sent' as type"))
                                       ->where('client_id', $client->id)                                     
                                       ->orderBy('created_at', 'desc')
                                       ->get(); 
                }
                else{
                    $invoice = invoice::select('id', 'date','created_at','paymentamount','ttc','status',DB::raw("null as invoice_number"), DB::raw("'received' as type"))
                    ->where('client_id', $client->id);
                
                    $received_invoice = received_invoice::select('id', 'date','created_at','paymentamount','ttc' ,'status','invoice_number', DB::raw("'sent' as type"))
                    ->where('client_id', $client->id);
                  

                    $invoices=$invoice->union($received_invoice) 
                                ->orderBy('created_at', 'desc')
                                ->get();
                       
                }

            


        } 
    
        return view('sort_invoice',compact('invoices')); 
               
    }




    
    public function sort_devis(Request $request)
    {
        $user = Auth::user();
        $devis = [];
    
    
        
            $client = client::where('user_id', $user->id)->wherenull('state')->first();
            $filter = $request->input('filter');    
        
            if ($client) {
                if ($filter === 'received') {
                    $devis = devis::select('*', DB::raw("'received' as type"))
                                       ->where('client_id', $client->id)
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                                       
                } 
                elseif($filter === 'sent') 
                {
                    $devis = devis_recu::select('*', DB::raw("'sent' as type"))
                                       ->where('client_id', $client->id)                                     
                                       ->orderBy('created_at', 'desc')
                                       ->get();
                }
                else{
                    $l_devis = devis::select('id', 'date','created_at','is_confirmed',DB::raw("null as devis_number"), DB::raw("'received' as type"))
                    ->where('client_id', $client->id);
                
                    $received_devis = devis_recu::select('id', 'date','created_at','is_confirmed','devis_number', DB::raw("'sent' as type"))
                    ->where('client_id', $client->id);
                  

                    $devis=$l_devis->union($received_devis) 
                                ->orderBy('created_at', 'desc')
                                ->get();

                }

            


        } 
    
        return view('sort_devis',compact('devis')); 
               
    }


    
    public function dashboard_client()
    {
      $invoice_count=$this->invoice_count();
       $ttc_chart=$this->ttc_chart();
       $paymentamount_chart=$this->paymentamount_chart();
       return view('dashboard', compact('invoice_count','ttc_chart','paymentamount_chart'));
    
}


public function invoice_count()
{
    
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
               'data' => $receivedValues,
               'color' => '#1f77b4' // Couleur pour les factures rédigées
           ],
           [
               'name' => 'Factures Reçues',
               'data' => $draftValues,
               'color' => '#ff7f0e' // Couleur pour les factures reçues
           ]
       ]);

   return $chart;
}

public function ttc_chart()
{
    $draftItems = $this->trimestre_data_currentYear('invoices');
    $receivedItems = $this->trimestre_data_currentYear('received_invoices');

    $labels = [];
    $draftValues = [];
    $receivedValues = [];
    // Group data by quarter
    $draftGrouped = $draftItems->groupBy('quarter');
    $receivedGrouped = $receivedItems->groupBy('quarter');

    foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
         // Somme des TTC pour les factures rédigées
         $draftTtc = $draftGrouped->get($quarter, collect())->sum('ttc');

         // Somme des TTC pour les factures reçues
         $receivedTtc = $receivedGrouped->get($quarter, collect())->sum('ttc');
 
         $labels[] = $quarter;
         $draftValues[] = $draftTtc;
         $receivedValues[] = $receivedTtc;
    }

    $chart = (new LarapexChart)->barChart()
        ->setTitle('Total TTC par Trimestre')
        ->setSubtitle('Répartition trimestrielle du TTC des factures rédigées et reçues par utilisateur')
        ->setXAxis($labels)
        ->setDataset([
            [
                'name' => 'TTC Factures Rédigées',
                'data' => $receivedValues,
                'color' => '#1f77b4' // Couleur pour les factures rédigées
            ],
            [
                'name' => 'TTC Factures Reçues',
                'data' => $draftValues,
                'color' => '#ff7f0e' // Couleur pour les factures reçues
            ]
        ]);

    return $chart;
}


public function paymentamount_chart()
{
    $draftItems = $this->trimestre_data_currentYear('invoices');
    $receivedItems = $this->trimestre_data_currentYear('received_invoices');

    $labels = [];
    $draftValues = [];
    $receivedValues = [];
    // Group data by quarter
    $draftGrouped = $draftItems->groupBy('quarter');
    $receivedGrouped = $receivedItems->groupBy('quarter');

    foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
         // Somme des TTC pour les factures rédigées
         $draftTtc = $draftGrouped->get($quarter, collect())->sum('paymentamount');

         // Somme des TTC pour les factures reçues
         $receivedTtc = $receivedGrouped->get($quarter, collect())->sum('paymentamount');
 
         $labels[] = $quarter;
         $draftValues[] = $draftTtc;
         $receivedValues[] = $receivedTtc;
    }

    $chart = (new LarapexChart)->barChart()
        ->setTitle('Total des paiements par Trimestre')
        ->setSubtitle('Répartition trimestrielle des paiements réellement reçus et payés')
        ->setXAxis($labels)
        ->setDataset([
            [
                'name' => 'Paiements reçus pour Factures Rédigées',
                'data' => $receivedValues,
                'color' => '#1f77b4' // Couleur pour les factures rédigées
            ],
            [
                'name' => 'Paiements effectués pour Factures Reçues',
                'data' => $draftValues,
                'color' => '#ff7f0e' // Couleur pour les factures reçues
            ]
        ]);

    return $chart;
}


public function trimestre_data_currentYear($table)
{  $userId = Auth::user()->id;
    $clientIds = Client::where('user_id', $userId)->pluck('id');
    
    $currentYear = date('Y');
    $query = DB::table($table)
        ->select(DB::raw(
            "strftime('%Y', date) as year,
            CASE
                WHEN strftime('%m', date) IN ('01', '02', '03') THEN 'Q1'
                WHEN strftime('%m', date) IN ('04', '05', '06') THEN 'Q2'
                WHEN strftime('%m', date) IN ('07', '08', '09') THEN 'Q3'
                WHEN strftime('%m', date) IN ('10', '11', '12') THEN 'Q4'
            END as quarter,
            *
            "
        ))
        ->orderBy('year')
        ->orderBy('quarter')
        ->whereYear('date', $currentYear);

       
        $query->wherein('client_id', $clientIds); 

    return $query->get();
}

/*
public function trimestre_data_currentYear_item($table,$table_item)
{  $userId = Auth::user()->id;
    $client=client::where('user_id',$userId)->first();
    
    $invoiceIds = DB::table($table)->where('client_id', $client->id)->pluck('id');

       $col_name= $table==='invoices'?'invoice_id':'received_invoice_id';
    $currentYear = date('Y');
    $query = DB::table($table_item)
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
        ->whereIn($col_name, $invoiceIds)
        ->orderBy('year')
        ->orderBy('quarter')
        ->whereYear('created_at', $currentYear);
       
     

    return $query->get();
}

*/

/*
    public function invoice_count()
    {
        $clientId = Auth::user()->id; // Get the ID of the currently authenticated user
        $invoices = $this->trimestre_data_currentYear('invoices', $clientId); // Pass the client ID to the function
        $labels = [];
        $values = [];
    
        $invoiceCount = $invoices->groupBy(['quarter']);
    
        foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $quarter) {
            $count = isset($invoiceCount[$quarter]) ? $invoiceCount[$quarter]->count() : 0;
            $labels[] = $quarter;  
            $values[] = $count;   
        }
    
        $chart = (new LarapexChart)->barChart()
            ->setTitle('Nombre de Factures Reçues par Trimestre')
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
    
    public function ttc_chart()
    {
        $clientId =Auth::user()->id; // Get the ID of the currently authenticated user
        $items = $this->trimestre_data_currentYear('invoice_items', $clientId); // Pass the client ID to the function
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
            ->setTitle('Total TTC par Trimestre Reçues')
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
    

    public function trimestre_data_currentYear($table, $clientId = null)
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
        
    $query->whereYear('created_at', $currentYear);

    if ($clientId) {
        $query->where('client_id', $clientId); // Filter by client ID
    }
   
    return $query->get();
}
*/
    function test(){
        return view('404');
    }
}
