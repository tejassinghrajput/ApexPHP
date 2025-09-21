<?php
namespace App\Modules\Payments;
use App\Core\Controller;
use App\Core\Request;
class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        return $this->json(PaymentsModel::all());
    }

    public function show(Request $request, int $id)
    {
        $item = PaymentsModel::find($id);
        if (!$item) {
            return $this->json(['message' => 'Not Found'], 404);
        }
        return $this->json($item);
    }
}