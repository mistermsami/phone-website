<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        {{ config('app.name') }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <!-- External CSS libraries -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">
    {{-- <link type="text/css" rel="stylesheet"
        href="{{ asset('assets/invoice/fonts/font-awesome/css/font-awesome.min.css') }}"> --}}
    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Custom Stylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
    <style>
        @media only screen and (max-width: 480px) {
            #invoice_wrapper {
                width: 100% !important;
                padding: 10px;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-16 invoice-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="invoice-inner-9" id="invoice_wrapper">
                        <div class="invoice-top">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6">
                                    <div class="logo">
                                        {{-- <h1>{{ Str::title(auth()->user()->name) }}</h1> --}}
                                        <img src="{{ asset('assets/img/logo.PNG') }}" style="width: 125px; height:125px"
                                            alt="Panther Force">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="invoice">
                                        <h1>
                                            Invoice # <span>{{ $order->invoice_no }}</span>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="invoice-info">
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <div class="invoice-number">
                                        <h4 class="inv-title-1">
                                            Invoice date:
                                        </h4>
                                        <p class="invo-addr-1">
                                            {{ $order->order_date }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <h4 class="inv-title-1">Customer</h4>
                                    <p class="inv-from-1">{{ $order->customer->name }}</p>
                                    <p class="inv-from-1">{{ $order->customer->phone }}</p>
                                    <p class="inv-from-1">{{ $order->customer->email }}</p>
                                </div>
                                @php
                                    $user = auth()->user();
                                @endphp
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">Store</h4>
                                    <p class="inv-from-1">{{ Str::title($order->customer->store_address) }}</p>
                                    <p class="inv-from-1">{{ $order->customer->address }}</p>
                                    <p class="inv-from-1">{{ $user->store_email }}</p>
                                    <p class="inv-from-2">{{ $user->store_address }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="table-outer">
                                <table class="default-table invoice-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">SKU</th>
                                            <th class="align-middle">Item</th>
                                            <th class="align-middle text-center">Price</th>
                                            <th class="align-middle text-center">Quantity</th>
                                            <th class="align-middle text-center">Subtotal</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {{--                                            @foreach ($orderDetails as $item) --}}
                                        @foreach ($order->details as $item)
                                            <tr>
                                                <td class="align-middle">
                                                    {{ $item->product->sku }}
                                                </td>
                                                <td class="align-middle">
                                                    {{ $item->product->name }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ Number::currency($item->unitcost, 'GBP') }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="align-middle text-center">
                                                    {{ Number::currency($item->total, 'GBP') }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <strong>
                                                    Subtotal
                                                </strong>
                                            </td>
                                            <td class="align-middle text-center">
                                                <strong>
                                                    {{ Number::currency($order->sub_total, 'GBP') }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <strong>Tax</strong>
                                            </td>
                                            <td class="align-middle text-center">
                                                <strong>
                                                    {{ Number::currency($order->vat, 'GBP') }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <strong>Total</strong>
                                            </td>
                                            <td class="align-middle text-center">
                                                <strong>
                                                    {{ Number::currency($order->total, 'GBP') }}
                                                </strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- <div class="invoice-informeshon-footer">
                                <ul>
                                    <li><a href="#">www.website.com</a></li>
                                    <li><a href="mailto:sales@hotelempire.com">info@example.com</a></li>
                                    <li><a href="tel:+088-01737-133959">+62 123 123 123</a></li>
                                </ul>
                            </div> --}}
                    </div>
                    <div class="invoice-btn-section clearfix d-print-none">
                        <a href="javascript:window.print()" class="btn btn-lg btn-print">
                            <i class="fa fa-print"></i>
                            Print Invoice
                        </a>
                        <a id="invoice_download_btn" class="btn btn-lg btn-download">
                            <i class="fa fa-download"></i>
                            Download Invoice
                        </a>
                    </div>

                    {{-- back button --}}
                    <div class="invoice-btn-section clearfix d-print-none">
                        <a href="{{ route('orders.index') }}" class="btn btn-lg btn-print">
                            <i class="fa fa-arrow-left"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/app.js') }}"></script>
</body>

</html>
