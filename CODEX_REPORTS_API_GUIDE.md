# Reports API Guide For Codex And Expo

This project now exposes the report layer as JSON for the Expo app.

## Auth model

The new report endpoints use the existing Laravel `auth` session middleware.

- First call `POST /login` with `username` and `password`.
- Keep the Laravel session cookie for later requests.
- Then call the report endpoints with the same cookie jar.

This is important: there is no token-based mobile auth in this repo yet. The Expo app should either:

- use a cookie-aware HTTP client flow, or
- add token auth later as a separate backend change.

## Endpoints

### 1. List all available reports

`GET /api/reports`

Returns:

- authenticated client context
- default dates
- all report keys
- supported filters for each report
- endpoint URL for each report

### 2. Load one report

`GET /api/reports/{reportKey}`

Supported `reportKey` values:

- `showroom-wise-sales`
- `sales-summary`
- `sales-details`
- `sales-summary-invoice`
- `sales-details-invoice`
- `collection`
- `gross-profit-loss`
- `item-wise-gross-profit-loss`
- `invoice-wise-gross-profit-loss`
- `daily-expense`
- `bank-deposit`
- `bank-withdraw`
- `supplier-payment`
- `none-sale-income`
- `present-stock`
- `net-profit-loss`

## Query parameters

The API accepts both the existing web-style params and cleaner mobile params:

- `shop_id` or `shopId`
- `start_date` or `startDate`
- `end_date` or `endDate`
- `category` or `cat` for `present-stock`

Date rules:

- `start_date` and `end_date` must be valid dates
- `end_date` must be on or after `start_date`

## Response shape

Every successful response uses:

```json
{
  "success": true,
  "message": "Report loaded successfully",
  "data": {}
}
```

Each report payload contains at least:

```json
{
  "key": "sales-summary",
  "title": "Sales Summary",
  "description": "Date-wise sales summary totals.",
  "filters": {
    "shop_id": "SHOP-01",
    "start_date": "2026-03-01",
    "end_date": "2026-03-14",
    "category": null
  },
  "summary": {},
  "rows": []
}
```

Some reports also include richer fields:

- `shops`
- `categories`
- `category_breakdown`
- `grouped_rows`
- `grouped_by_shop`
- `invoices`
- `pay_types`
- `table`
- `column_totals`
- `chunk_size`

## Expo integration pattern

Suggested client flow:

1. Sign in with `POST /login`.
2. Persist the session cookie.
3. Call `GET /api/reports` to build the report menu dynamically.
4. Call `GET /api/reports/{reportKey}` when a user opens a report screen.
5. Build screen components from `summary`, `rows`, and any extra matrix/group fields.

## Example requests

### Sales summary

```http
GET /api/reports/sales-summary?shop_id=SHOP-01&start_date=2026-03-01&end_date=2026-03-14
```

### Present stock

```http
GET /api/reports/present-stock?shop_id=SHOP-01&category=ELECTRONICS
```

### Daily expense

```http
GET /api/reports/daily-expense?start_date=2026-03-01&end_date=2026-03-14
```

## Codex build notes

If another Codex agent is building the Expo app from this API, it should assume:

- report discovery comes from `GET /api/reports`
- each report screen should be generated from the `key`
- filters should be rendered from the `filters` list in the index response
- session-cookie auth is required
- matrix-style reports should render `table` plus `column_totals`
- invoice detail screens should prefer the `invoices` array over raw `rows`

## Recommended screen mapping

- `showroom-wise-sales`: bar chart + category breakdown list
- `sales-summary`: line or bar chart by date
- `sales-details`: grouped section list by category
- `sales-summary-invoice`: invoice list grouped by shop
- `sales-details-invoice`: invoice cards with nested items
- `collection`: matrix table by date and payment type
- `gross-profit-loss`: date-wise KPI list or chart
- `item-wise-gross-profit-loss`: grouped item profitability list
- `invoice-wise-gross-profit-loss`: invoice profitability list
- `daily-expense`: expense matrix
- `bank-deposit`: expense matrix
- `bank-withdraw`: expense matrix
- `supplier-payment`: expense matrix
- `none-sale-income`: income matrix
- `present-stock`: searchable stock list
- `net-profit-loss`: date-wise profit trend

## Future upgrade path

For a cleaner Expo production setup, the next backend improvement should be token auth such as Laravel Sanctum or Passport. That work is not included in this change.
