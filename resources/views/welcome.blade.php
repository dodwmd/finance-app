<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Vibe Finance - Take control of your financial future">

        <title>Vibe Finance - Personal Finance Tracker</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                50: '#f0f9ff',
                                100: '#e0f2fe',
                                200: '#bae6fd',
                                300: '#7dd3fc',
                                400: '#38bdf8',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                                800: '#075985',
                                900: '#0c4a6e',
                            },
                        },
                    },
                },
            }
        </script>
        
        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    </head>
    <body class="antialiased bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 mr-4">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-indigo-200 dark:border-indigo-800">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center">
                    <div class="text-center">
                        <h1 class="text-5xl md:text-6xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">Vibe Finance</h1>
                        <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300">Take control of your financial future</p>
                    </div>
                </div>

                <div class="mt-16">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">
                        <!-- App Description Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-8">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Your Personal Finance Tracker</h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                                Vibe Finance helps you track your income, expenses, and savings goals. Get insights into your spending habits and make informed financial decisions.
                            </p>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">Easy expense tracking and categorization</span>
                                </div>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">Detailed financial reports and analytics</span>
                                </div>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">Budget planning and goal setting</span>
                                </div>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">Secure and private financial data</span>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action Section -->
                        <div class="bg-indigo-600 dark:bg-indigo-700 rounded-xl shadow-md overflow-hidden p-8 text-white">
                            <h2 class="text-2xl font-semibold mb-6">Start Your Financial Journey Today</h2>
                            <p class="leading-relaxed mb-8">
                                Join thousands of users who have already taken control of their finances with Vibe Finance. Sign up for free and start tracking your money.
                            </p>
                            <div class="space-y-4">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block w-full text-center bg-white text-indigo-600 hover:bg-gray-100 font-semibold py-3 px-6 rounded-lg transition duration-200">
                                        Create Free Account
                                    </a>
                                @endif
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="block w-full text-center bg-transparent hover:bg-indigo-700 font-semibold py-3 px-6 border border-white rounded-lg transition duration-200">
                                        Sign In
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-8">How It Works</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <div class="flex justify-center items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Track Transactions</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Easily add your income and expenses. Categorize and tag them for better tracking.
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <div class="flex justify-center items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Analyze Your Spending</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                View detailed reports and charts to understand where your money goes each month.
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                            <div class="flex justify-center items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Achieve Your Goals</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Set savings goals and track your progress. Stay motivated and reach financial freedom.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center mt-16 px-0 sm:items-center">
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-4 justify-center">
                            <a href="#" class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                                About Us
                            </a>
                            <a href="#" class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                            <a href="#" class="group inline-flex items-center hover:text-gray-700 dark:hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" />
                                </svg>
                                Contact
                            </a>
                        </div>
                        <div class="mt-4">
                            &copy; {{ date('Y') }} Vibe Finance. All rights reserved.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
