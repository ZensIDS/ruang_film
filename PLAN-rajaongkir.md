# RajaOngkir + Komship Shipping Integration Plan

## Summary
- Keep Laravolt as the address-entry source for biodata and checkout.
- Replace the fixed-fee expedition dropdown with live RajaOngkir shipping quotes filtered by the active expeditions configured in the app.
- Keep the current manual payment flow unchanged; only after an order is `paid` can admin create the external Komship shipment, capture the order number / AWB, and refresh tracking.

## Key Changes
- Add `services.rajaongkir` config for:
  - Core API client using `RAJAONGKIR_BASE_URL` + `RAJAONGKIR_API_KEY_SHIPPING_COST` with `key` header.
  - Delivery client using new `RAJAONGKIR_ORDER_BASE_URL` defaulting to `https://api.collaborator.komerce.id/order/api/v1` + `RAJAONGKIR_API_KEY_SHIPPING_DELIVERY` with `x-api-key` header.
  - Retry/timeout/courier defaults from the existing env values.
  - Env-backed shipper profile: `RAJAONGKIR_SHIPPER_BRAND_NAME`, `RAJAONGKIR_SHIPPER_NAME`, `RAJAONGKIR_SHIPPER_PHONE`, `RAJAONGKIR_SHIPPER_EMAIL`, `RAJAONGKIR_SHIPPER_ADDRESS`, optional `RAJAONGKIR_ORIGIN_PIN_POINT`, and `RAJAONGKIR_ORIGIN_DESTINATION_ID` with fallback to the existing `RAJAONGKIR_ORIGIN_DISTRICT_ID`.
- Add a shipping service layer:
  - `RajaOngkirCostService` for domestic destination search, destination resolution, cost calculation, and generic waybill tracking.
  - `RajaOngkirDeliveryService` for Komship order store, order detail, and AWB history.
  - `RajaOngkirDestinationResolver` to map the saved Laravolt location into a RajaOngkir destination ID by searching with `desa + kecamatan + kabupaten + provinsi`, then matching exact normalized location parts before falling back to a label match.
- Extend expedition data so active couriers can be used for live quotes:
  - Add `external_code` to `expeditions` and use it as the RajaOngkir courier code.
  - Backfill the seeded rows to `jne`, `jnt`, and `sicepat`.
  - Keep `fee` and `service_name` for backward compatibility, but stop using `fee` in checkout pricing.
- Extend order data for shipping lifecycle without changing payment status semantics:
  - Add `expedition_code`, `expedition_service_code`, `shipping_etd`, `shipping_destination_id`, `shipping_order_no`, `shipping_airway_bill`, `shipping_status`, `shipping_status_label`, `shipping_payload`, `shipping_tracking_payload`, and `shipping_synced_at`.
  - Keep the existing `status` column as payment status only.
- Update checkout flow:
  - Add an authenticated JSON endpoint `POST /checkout/shipping-options` that accepts the current shipping form values, resolves the RajaOngkir destination ID, computes total cart weight, calls live cost calculation, and returns quote rows grouped by active expeditions.
  - Change the checkout page to load and render courier/service options dynamically instead of a static expedition dropdown.
  - On final submit, `CheckoutController@store` must re-fetch the selected quote server-side and persist the authoritative courier, service, destination ID, fee, and ETA; it must not trust a client-submitted shipping fee.
  - If destination resolution fails or the selected quote disappears, abort checkout with a validation-style error and keep the cart untouched.
- Update admin fulfillment flow:
  - Add `POST /admin/orders/{order}/shipment` to create the Komship shipment for `paid` orders that do not yet have `shipping_order_no`.
  - Add `POST /admin/orders/{order}/shipment/sync` to refresh detail and tracking, update AWB/status/timeline, and keep the latest raw payloads.
  - Make shipment creation idempotent by blocking duplicate create calls once `shipping_order_no` exists.
  - Show shipment metadata, AWB, latest shipping status, and timeline on the admin order detail page.
  - Show stored AWB and latest shipping status on the customer invoice/history views; tracking refresh stays admin-driven in v1.

## Public Interfaces / Types
- New env/config surface:
  - `RAJAONGKIR_ORDER_BASE_URL`
  - `RAJAONGKIR_SHIPPER_BRAND_NAME`
  - `RAJAONGKIR_SHIPPER_NAME`
  - `RAJAONGKIR_SHIPPER_PHONE`
  - `RAJAONGKIR_SHIPPER_EMAIL`
  - `RAJAONGKIR_SHIPPER_ADDRESS`
  - `RAJAONGKIR_ORIGIN_DESTINATION_ID`
  - `RAJAONGKIR_ORIGIN_PIN_POINT`
- New routes:
  - `POST /checkout/shipping-options`
  - `POST /admin/orders/{order}/shipment`
  - `POST /admin/orders/{order}/shipment/sync`
- New normalized shipping status values:
  - `pending`
  - `booked`
  - `in_transit`
  - `delivered`
  - `failed`

## Test Plan
- Add service tests with `Http::fake()` covering:
  - destination search and exact destination resolution
  - domestic cost normalization
  - Komship order creation normalization
  - detail/history sync normalization
  - API failure and timeout handling
- Add checkout feature tests covering:
  - live shipping options returned for a valid Laravolt address
  - checkout stores live quote data instead of expedition `fee`
  - checkout rejects a tampered or stale selected quote
  - checkout fails cleanly when destination cannot be resolved
- Add admin fulfillment feature tests covering:
  - admin can create shipment only for `paid` orders
  - duplicate shipment creation is blocked
  - sync updates `shipping_order_no`, AWB, status, and stored payloads
  - non-admin users cannot call shipment actions
- Update existing admin expedition CRUD tests so expedition creation includes `external_code`.

## Assumptions
- `RAJAONGKIR_API_KEY_PAYMENT` is not used in this phase because payment remains manual transfer.
- Pickup requests, label printing, order cancellation, and webhook ingestion stay out of scope for this first pass.
- Laravolt remains the source of truth for displayed address fields; RajaOngkir destination IDs are derived only for shipping operations.
- If RajaOngkir cannot resolve a destination with a confident exact match, the app should fail fast instead of guessing and risking a wrong shipment.
