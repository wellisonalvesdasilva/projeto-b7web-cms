<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Visitor;
use App\Page;
use App\User;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $onlineCount = 2;
        $visitsCount = 101;
        $pageCount = 6;
        $userCount = 9;
        $interval = intval($request->input('interval', 30));

        if ($interval > 120) {
            $interval = 120;
        }

        $dateInterval = date('Y-m-d H:i:s', strtotime('-' . $interval . ' days'));
        $visitsCount = Visitor::where('date_access', '>=', $dateInterval)->count();

        // Contagem de Usuários Online
        $datelimit = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $onlineList = Visitor::select('ip')->where('date_access', '>=', $datelimit)->groupBy('ip')->get();
        $onlineCount = count($onlineList);
        // Contagem de páginas
        $pageCount = Page::count();

        // Contagem de usuários
        $userCount = User::count();

        $pagePie = [];
        // Contagem para o PagePie
        $visitsAll = Visitor::selectRaw('page, count(page) as c')
            ->where('date_access', '>=', $dateInterval)
            ->groupBy('page')
            ->get();

        foreach ($visitsAll as $visit) {
            $pagePie[$visit['page']] = intval($visit['c']);
        }

        $pageLabels = json_encode(array_keys($pagePie));
        $pageValues = json_encode(array_values($pagePie));

        return view('admin.home', [
            'visitsCount' => $visitsCount,
            'onlineCount' => $onlineCount,
            'pageCount' => $pageCount,
            'userCount' => $userCount,
            'pageLabels' => $pageLabels,
            'pageValues' => $pageValues,
            'dateInterval' => $interval
        ]);
    }
}
