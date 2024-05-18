<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use App\Mail\StockAlert;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;

class OrderController extends Controller
{
	public function index()
	{
		if (auth()->user()->role === 'supplier' or auth()->user()->role !== 'supplier') {
			// dd("asd");
			$orders = Order::all()->count();
		} else {
			$orders = Order::where('user_id', auth()->id())->count();
		}

		return view('orders.index', [
			'orders' => $orders
		]);
	}

	public function create()
	{
		// $products = Product::where('user_id', auth()->id())->with(['category_id'])->get();
		$products = Product::with(['category_id'])->get();

		if (auth()->user()->role == 'admin' || auth()->user()->role == 'supplier') {
			$customers = Customer::get(['id', 'name']);
		} else {
			// $customers = Customer::where('user_id', auth()->id())->get(['id', 'name']);
			$customers = Customer::get(['id', 'name']);
		}

		$carts = Cart::content();

		return view('orders.create', [
			'products' => $products,
			'customers' => $customers,
			'carts' => $carts,
		]);
	}

	public function store(OrderStoreRequest $request)
	{
		$order = Order::create([
			'customer_id' => $request->customer_id,
			'payment_type' => $request->payment_type,
			'pay' => $request->pay,
			'order_date' => Carbon::now()->format('Y-m-d'),
			'order_status' => OrderStatus::PENDING->value,
			'total_products' => Cart::count(),
			'sub_total' => Cart::subtotal(),
			'vat' => Cart::tax(),
			'total' => Cart::total(),
			'invoice_no' => IdGenerator::generate([
				'table' => 'orders',
				'field' => 'invoice_no',
				'length' => 10,
				'prefix' => 'INV-'
			]),
			'due' => (Cart::total() - $request->pay),
			'user_id' => auth()->id(),
			'uuid' => Str::uuid(),
		]);

		// Create Order Details
		$contents = Cart::content();
		$oDetails = [];

		foreach ($contents as $content) {
			$oDetails['order_id'] = $order['id'];
			$oDetails['product_id'] = $content->id;
			$oDetails['quantity'] = $content->qty;
			$oDetails['unitcost'] = $content->price;
			$oDetails['total'] = $content->subtotal;
			$oDetails['created_at'] = Carbon::now();

			OrderDetails::insert($oDetails);
		}

		// Delete Cart Sopping History
		Cart::destroy();

		return redirect()
			->route('orders.index')
			->with('success', 'Order has been created!');
	}

	public function show($uuid)
	{
		$order = Order::where('uuid', $uuid)->firstOrFail();
		$order->loadMissing(['customer', 'details'])->get();
		return view('orders.show', [
			'order' => $order
		]);
	}

	public function update($uuid, Request $request)
	{

		ini_set('max_execution_time', 120);
		$order = Order::with(['customer', 'details'])->where('uuid', $uuid)->firstOrFail();

		$order = Order::where('uuid', $uuid)->firstOrFail();
		// TODO refactoring

		// Reduce the stock
		$products = OrderDetails::where('order_id', $order->id)->get();

		$stockAlertProducts = [];

		foreach ($products as $product) {
			$productEntity = Product::where('id', $product->product_id)->first();
			$newQty = $productEntity->quantity - $product->quantity;
			if ($newQty < $productEntity->quantity_alert) {
				$stockAlertProducts[] = $productEntity;
			}
			$productEntity->update(['quantity' => $newQty]);
		}

		if (count($stockAlertProducts) > 0) {
			$listAdmin = [];
			foreach (User::all('email') as $admin) {
				$listAdmin[] = $admin->email;
			}
			Mail::to($listAdmin)->send(new StockAlert($stockAlertProducts));
		}
		$operation = $order->update([
			'order_status' => OrderStatus::COMPLETE,
			'due' => '0',
			'pay' => $order->total
		]);
		if ($operation) {
			$data = [
				"email" => "kamranafridi089@gmail.com",
				"title" => "From pantherforce.co.uk",
				"body" => 'Invoice',
			];
			$pdf = PDF::loadView('emails.invoice', compact('order'));
			// dd($pdf);
			Mail::send('emails.message', $data, function ($message) use ($data, $pdf) {
				$message->to($data["email"], $data["email"])
					->subject($data["title"])
					->attachData($pdf->output(), "Invoice.pdf", [
						'mime' => 'application/pdf',
					]);
			});
		}
		return redirect()
			->route('orders.complete')
			->with('success', 'Order has been completed!');
	}

	public function destroy($uuid)
	{
		$order = Order::where('uuid', $uuid)->firstOrFail();
		$order->delete();
	}

	public function downloadInvoice($uuid)
	{
		$order = Order::with(['customer', 'details'])->where('uuid', $uuid)->firstOrFail();
		// TODO: Need refactor
		//dd($order);

		//$order = Order::with('customer')->where('id', $order_id)->first();
		// $order = Order::
		//     ->where('id', $order)
		//     ->first();

		return view('orders.print-invoice', [
			'order' => $order,
		]);
	}

	public function cancel(Order $order)
	{
		$order->update([
			'order_status' => 2
		]);
		$orders = Order::where('user_id', auth()->id())->count();

		return redirect()
			->route('orders.index', [
				'orders' => $orders
			])
			->with('success', 'Order has been canceled!');
	}
}
