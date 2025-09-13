<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        $trucks = Truck::with('user', 'category')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);
        return view('admin.trucks.index', compact('trucks'));
    }

    public function show(Truck $truck)
    {
        return view('admin.trucks.show', compact('truck'));
    }

    public function updateStatus(Request $request, Truck $truck)
    {
        $request->validate(['status' => 'required|in:active,inactive,pending']);
        $truck->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث حالة الشاحنة بنجاح.');
    }
}