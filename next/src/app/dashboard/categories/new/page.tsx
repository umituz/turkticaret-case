'use client';

import { CategoryForm } from '@/components/admin/CategoryForm';

export default function NewCategoryPage() {
  return (
    <div className="container mx-auto py-6">
      <CategoryForm mode="create" />
    </div>
  );
}