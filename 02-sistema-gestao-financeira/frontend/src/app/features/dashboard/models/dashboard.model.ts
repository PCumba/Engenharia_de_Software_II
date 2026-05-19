export interface FinancialSummary {
  totalBalance: number;
  monthlyIncome: number;
  monthlyExpense: number;
  monthlyBalance: number;
  previousMonthBalance: number;
  balanceChange: number;
  balanceChangePercentage: number;
}

export interface ExpenseByCategory {
  category: string;
  color: string;
  total: number;
  count: number;
  percentage?: number;
}

export interface RecentTransaction {
  id: number;
  type: 'income' | 'expense' | 'transfer';
  amount: number;
  description: string;
  transaction_date: string;
  category_name?: string;
  category_color?: string;
  account_name?: string;
  status: 'pending' | 'completed' | 'cancelled';
}

export interface GoalProgress {
  id: number;
  name: string;
  target_amount: number;
  current_amount: number;
  target_date?: string;
  category: string;
  priority: 'low' | 'medium' | 'high';
  percentage: number;
}

export interface MonthlyEvolution {
  month: string;
  income: number;
  expense: number;
  balance: number;
}

export interface AccountSummary {
  id: number;
  name: string;
  type: string;
  current_balance: number;
  currency: string;
  color: string;
  icon: string;
}

export interface BudgetStatus {
  id: number;
  name: string;
  category_name?: string;
  amount: number;
  spent: number;
  remaining: number;
  percentage: number;
  status: 'ok' | 'warning' | 'exceeded';
}

export interface DashboardData {
  summary: FinancialSummary;
  expensesByCategory: ExpenseByCategory[];
  recentTransactions: RecentTransaction[];
  goals: GoalProgress[];
  monthlyEvolution: MonthlyEvolution[];
  accounts: AccountSummary[];
  budgets: BudgetStatus[];
  alerts: DashboardAlert[];
}

export interface DashboardAlert {
  id: number;
  type: string;
  title: string;
  message: string;
  priority: 'low' | 'medium' | 'high';
  created_at: string;
}