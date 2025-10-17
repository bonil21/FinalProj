# TODO: Update Revenue Tracking to Use Payments

## Tasks
- [x] Add revenue calculation methods to PaymentRepository (getTodayRevenue, getTotalRevenue, getMonthlyRevenue)
- [x] Update AdminController to use PaymentRepository for revenue data instead of OrderRepository
- [x] Update TODO.md to reflect completed tasks

## Information Gathered
- Current revenue is calculated from Order.totalAmount in OrderRepository::getTodayRevenue()
- Payment entity has amount field and status field (likely 'succeeded' for completed payments)
- Admin dashboard displays todayRevenue from orders
- PaymentRepository exists but lacks revenue methods

## Plan
1. Add methods to PaymentRepository:
   - getTodayRevenue(): Sum amount where status = 'succeeded' and createdAt >= today
   - getTotalRevenue(): Sum amount where status = 'succeeded'
   - getMonthlyRevenue(): Sum amount where status = 'succeeded' and createdAt in current month
2. Update AdminController index method to inject PaymentRepository and use getTodayRevenue() for todayRevenue
3. Update TODO.md to reflect completed tasks

## Dependent Files
- src/Repository/PaymentRepository.php (add revenue methods)
- src/Controller/AdminController.php (update revenue fetching)
- TODO.md (update status)

## Followup Steps
- Test admin dashboard to ensure revenue displays correctly
- Verify payment statuses are correctly filtering successful payments
