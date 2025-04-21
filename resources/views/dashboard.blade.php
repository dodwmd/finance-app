@extends('layouts.app')

@section('header')
    Dashboard
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Total Balance</p>
                    <p class="text-2xl font-bold text-gray-900">$10,250.00</p>
                </div>
                <div class="bg-green-100 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Monthly Change</span>
                    <span class="text-green-600">+$580.00 (5.6%)</span>
                </div>
            </div>
        </div>

        <!-- Expenses Card -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Expenses</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-900">$2,340.00</p>
                </div>
                <div class="bg-red-100 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Compared to last month</span>
                    <span class="text-red-600">+$140.00 (6.3%)</span>
                </div>
            </div>
        </div>

        <!-- Savings Card -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Savings Goal</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Progress</p>
                    <p class="text-2xl font-bold text-gray-900">$4,200.00</p>
                </div>
                <div class="bg-blue-100 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Target: $10,000.00</span>
                    <span class="text-blue-600">42%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 42%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h3>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Grocery Shopping</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Food</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 20, 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-$120.35</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Salary Deposit</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Income</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 15, 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+$3,200.00</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Coffee Shop</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dining</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 18, 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-$4.75</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Electric Bill</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Utilities</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 10, 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-$94.50</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Freelance Payment</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Income</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Apr 8, 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+$450.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
