<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Planning') }}
            </h2>
            <a href="{{ route('budgets.create') }}" dusk="create-budget" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Create Budget') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Budget Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Budgeted</h3>
                                <p class="text-2xl font-bold text-blue-600">${{ number_format($overview['total_budgeted'], 2) }}</p>
                            </div>
                            <div class="text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Spent</h3>
                                <p class="text-2xl font-bold text-red-600">${{ number_format($overview['total_spent'], 2) }}</p>
                            </div>
                            <div class="text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Remaining</h3>
                                <p class="text-2xl font-bold text-green-600">${{ number_format($overview['total_remaining'], 2) }}</p>
                            </div>
                            <div class="text-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Cards for Exceeded and Near Limit Budgets -->
            @if($overview['exceeded_count'] > 0 || $overview['near_limit_count'] > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @if($overview['exceeded_count'] > 0)
                        <div class="bg-red-50 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-red-200">
                                <h3 class="text-lg font-semibold text-red-700 mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $overview['exceeded_count'] }} Budget(s) Exceeded
                                </h3>
                                <div class="space-y-2">
                                    @foreach($overview['exceeded_budgets'] as $budget)
                                        <div class="flex justify-between items-center p-2 bg-white rounded shadow-sm">
                                            <div>
                                                <span class="font-medium">{{ $budget['budget']->name }}</span>
                                                <span class="text-sm text-gray-500 block">{{ $budget['budget']->category->name }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-red-600 font-semibold">${{ number_format($budget['spent'], 2) }} / ${{ number_format($budget['budget']->amount, 2) }}</span>
                                                <a href="{{ route('budgets.show', $budget['budget']->id) }}" class="text-blue-600 block text-sm">View</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($overview['near_limit_count'] > 0)
                        <div class="bg-yellow-50 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-yellow-200">
                                <h3 class="text-lg font-semibold text-yellow-700 mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $overview['near_limit_count'] }} Budget(s) Near Limit
                                </h3>
                                <div class="space-y-2">
                                    @foreach($overview['near_limit_budgets'] as $budget)
                                        <div class="flex justify-between items-center p-2 bg-white rounded shadow-sm">
                                            <div>
                                                <span class="font-medium">{{ $budget['budget']->name }}</span>
                                                <span class="text-sm text-gray-500 block">{{ $budget['budget']->category->name }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-yellow-600 font-semibold">${{ number_format($budget['spent'], 2) }} / ${{ number_format($budget['budget']->amount, 2) }}</span>
                                                <a href="{{ route('budgets.show', $budget['budget']->id) }}" class="text-blue-600 block text-sm">View</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 border-b border-gray-200">
                    <form action="{{ route('budgets.index') }}" method="GET" class="flex flex-col md:flex-row md:items-end space-y-2 md:space-y-0 md:space-x-4">
                        <div>
                            <label for="period" class="block text-sm font-medium text-gray-700">Filter by Period</label>
                            <select name="period" id="period" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All Periods</option>
                                @foreach($periodOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Filter
                            </button>
                            @if($period)
                                <a href="{{ route('budgets.index') }}" class="ml-2 text-indigo-600 hover:text-indigo-900 text-sm">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Active Budgets -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Active Budgets</h3>
                    
                    @if($activeBudgets->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($activeBudgets as $budget)
                                <div class="border rounded-lg shadow-sm overflow-hidden">
                                    <div class="p-4 border-b bg-gray-50">
                                        <h4 class="font-medium text-gray-800 truncate">{{ $budget['budget']->name }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ ucfirst($budget['budget']->period) }} | 
                                            {{ $budget['budget']->category->name }}
                                        </p>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">
                                                ${{ number_format($budget['spent'], 2) }} / ${{ number_format($budget['budget']->amount, 2) }}
                                            </span>
                                            <span class="text-sm font-medium {{ $budget['is_exceeded'] ? 'text-red-600' : 'text-gray-700' }}">
                                                {{ $budget['percentage'] }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="h-2.5 rounded-full {{ $budget['is_exceeded'] ? 'bg-red-600' : ($budget['percentage'] >= 80 ? 'bg-yellow-400' : 'bg-blue-600') }}" 
                                                 style="width: {{ min($budget['percentage'], 100) }}%"></div>
                                        </div>
                                        <div class="flex justify-between items-center mt-4">
                                            <div class="text-sm">
                                                <span class="text-gray-500">Period: </span>
                                                <span class="font-medium">
                                                    {{ \Carbon\Carbon::parse($budget['start_date'])->format('M d') }} - 
                                                    {{ \Carbon\Carbon::parse($budget['end_date'])->format('M d, Y') }}
                                                </span>
                                            </div>
                                            <div>
                                                <a href="{{ route('budgets.show', $budget['budget']->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No active budgets found for the selected period.</p>
                    @endif
                </div>
            </div>

            <!-- All Budgets Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">All Budgets</h3>
                    
                    @if($budgets->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($budgets as $budget)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $budget->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($budget->category)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                          style="background-color: {{ $budget->category->color }}20; color: {{ $budget->category->color }};">
                                                        <i class="fas fa-{{ $budget->category->icon }} mr-1"></i>
                                                        {{ $budget->category->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">No Category</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($budget->period) }}
                                                <span class="block text-xs text-gray-400">
                                                    {{ \Carbon\Carbon::parse($budget->start_date)->format('M d') }} - 
                                                    {{ $budget->end_date ? \Carbon\Carbon::parse($budget->end_date)->format('M d, Y') : 'Ongoing' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ number_format($budget->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($budget->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('budgets.show', $budget) }}" dusk="view-budget" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                                <a href="{{ route('budgets.edit', $budget) }}" dusk="edit-budget" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this budget?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $budgets->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No budgets found. <a href="{{ route('budgets.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first budget</a>.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
