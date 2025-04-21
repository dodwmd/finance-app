<?php

namespace App\Services;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TransactionService
{
    /**
     * The transaction repository instance.
     */
    protected $transactionRepository;

    /**
     * Create a new service instance.
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Create a new transaction.
     */
    public function createTransaction(array $data): Transaction
    {
        return $this->transactionRepository->create($data);
    }

    /**
     * Get user balance.
     */
    public function getUserBalance(int $userId): float
    {
        $income = $this->transactionRepository->getSumByType($userId, 'income');
        $expenses = $this->transactionRepository->getSumByType($userId, 'expense');

        return $income - $expenses;
    }

    /**
     * Get monthly summary.
     */
    public function getMonthlySummary(int $userId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $transactions = $this->transactionRepository->getByDateRange($userId, $startDate, $endDate);

        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses,
            'transactions' => $transactions,
            'month' => $month,
            'year' => $year,
        ];
    }

    /**
     * Get transactions by category.
     */
    public function getTransactionsByCategory(int $userId, int $categoryId): Collection
    {
        return $this->transactionRepository->getByUserId($userId)
            ->where('category_id', $categoryId);
    }

    /**
     * Get expense categories with totals.
     */
    public function getExpenseCategoriesWithTotals(int $userId): array
    {
        $expenses = $this->transactionRepository->getByUserIdAndType($userId, 'expense');
        
        $categories = [];
        foreach ($expenses as $expense) {
            $categoryId = $expense->category_id;
            
            if (!isset($categories[$categoryId])) {
                $category = Category::find($categoryId);
                $categoryName = $category ? $category->name : 'Uncategorized';
                
                $categories[$categoryId] = [
                    'name' => $categoryName,
                    'amount' => 0,
                    'color' => $category ? $category->color : '#607D8B',
                    'icon' => $category ? $category->icon : 'question-circle',
                ];
            }

            $categories[$categoryId]['amount'] += $expense->amount;
        }

        // Sort by highest amount
        uasort($categories, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return $categories;
    }
    
    /**
     * Get categories by type.
     */
    public function getCategoriesByType(int $userId, string $type = null): Collection
    {
        $query = Category::where('user_id', $userId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->orderBy('name')->get();
    }
}
