<?php
namespace App\Modules\Payments;
use App\Core\Controller;
use App\Core\Request;
class PaymentsController extends Controller
{
    public function index() { return $this->json(PaymentsModel::all()); }
    public function show(Request $request, int $id) { $item = PaymentsModel::find($id); return $item ? $this->json($item) : $this->json(['message' => 'Not Found'], 404); }
    public function store(Request $request) { $model = new PaymentsModel($request->json()); $model->save(); return $this->json($model, 201); }
    public function update(Request $request, int $id) { $model = PaymentsModel::find($id); if (!$model) { return $this->json(['message' => 'Not Found'], 404); } $model->fill($request->json()); $model->save(); return $this->json($model); }
    public function destroy(Request $request, int $id) { $model = PaymentsModel::find($id); if (!$model) { return $this->json(['message' => 'Not Found'], 404); } $model->delete(); return $this->json(null, 204); }
}