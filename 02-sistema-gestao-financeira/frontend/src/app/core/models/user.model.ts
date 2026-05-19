export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  timezone: string;
  currency: string;
  language: string;
  email_verified_at?: string;
  is_active: boolean;
  last_login_at?: string;
  created_at: string;
  updated_at: string;
}

export interface UserProfile extends User {
  // Campos adicionais para o perfil
  preferences?: UserPreferences;
  statistics?: UserStatistics;
}

export interface UserPreferences {
  theme: 'light' | 'dark' | 'auto';
  notifications: {
    email: boolean;
    push: boolean;
    budget_alerts: boolean;
    goal_reminders: boolean;
    transaction_notifications: boolean;
  };
  dashboard: {
    default_period: 'week' | 'month' | 'quarter' | 'year';
    show_balance: boolean;
    show_goals: boolean;
    show_recent_transactions: boolean;
  };
  privacy: {
    show_balance_in_list: boolean;
    require_auth_for_sensitive_actions: boolean;
  };
}

export interface UserStatistics {
  total_accounts: number;
  total_transactions: number;
  total_categories: number;
  total_goals: number;
  account_creation_date: string;
  last_transaction_date?: string;
  most_used_category?: string;
  average_monthly_income: number;
  average_monthly_expense: number;
}

export interface UpdateUserRequest {
  name?: string;
  phone?: string;
  timezone?: string;
  currency?: string;
  language?: string;
}

export interface ChangePasswordRequest {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}