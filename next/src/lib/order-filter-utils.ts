interface OrderFilters {
  page?: number;
  per_page?: number;
  status?: string;
  search?: string;
  date_from?: string;
  date_to?: string;
}

export function transformOrderFiltersForAPI(filters: {
  search?: string;
  status?: string;
  dateFilter?: string;
  page?: number;
  per_page?: number;
}): OrderFilters {
  const apiFilters: OrderFilters = {};

  if (filters.search) {
    apiFilters.search = filters.search;
  }

  if (filters.status && filters.status !== 'all') {
    apiFilters.status = filters.status;
  }

  if (filters.dateFilter && filters.dateFilter !== 'all') {
    const now = new Date();
    const filterDate = new Date();

    switch (filters.dateFilter) {
      case 'week':
        filterDate.setDate(now.getDate() - 7);
        break;
      case 'month':
        filterDate.setMonth(now.getMonth() - 1);
        break;
      case 'quarter':
        filterDate.setMonth(now.getMonth() - 3);
        break;
      case 'year':
        filterDate.setFullYear(now.getFullYear() - 1);
        break;
    }

    if (filters.dateFilter !== 'all') {
      apiFilters.date_from = filterDate.toISOString().split('T')[0];
    }
  }

  if (filters.page) {
    apiFilters.page = filters.page;
  }

  if (filters.per_page) {
    apiFilters.per_page = filters.per_page;
  }

  return apiFilters;
}