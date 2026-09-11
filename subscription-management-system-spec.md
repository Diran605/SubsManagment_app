# Subscription management system — requirements specification

## 1. Overview

An internal admin tool for tracking client businesses subscribed to the
company's various services. The company offers multiple services (e.g. a
POS system, a school management system), each deliverable in different
modes — **online**, **offline**, or **hybrid** — with mode-specific
pricing. When the company installs/activates a service for a business,
staff record the license details. The dashboard gives staff a single view
across all businesses, services, and subscriptions, with visibility into
who is nearing or past expiry so renewals can be followed up manually.

This is **not** a software licensing/DRM system — it does not gate
functionality inside any installed product. It is a record-keeping and
visibility tool for the company's internal team.

## 2. Users

- **Staff/admin only.** No client-facing login in this phase.
- **Single role — no permissions system in phase 1.** All staff accounts
  have equal access. Multi-role/permissions is deliberately deferred: it's
  real complexity with no concrete need yet at this team size. The only
  concession is a lightweight `created_by` field on key records (see §4)
  for accountability, without any actual access-control logic behind it.

## 3. Core concepts

| Term | Meaning |
|---|---|
| Service | A product the company offers (e.g. "POS System", "School Management System") |
| Mode | How a service is delivered: `online`, `offline`, or `hybrid` |
| Module | A single feature/building block of a service (e.g. "Payroll") |
| Package | A reusable named bundle of modules for a service (e.g. "Standard"), with a reference price — a shortcut for the common case; a subscription can also skip it and use a fully custom module selection |
| Service pricing | The company's fallback base/list price for a service + mode, used when quoting a custom (non-package) build |
| Business | A client company that has subscribed to one or more services |
| Subscription | A record linking one business to one service (in one mode), the actual set of modules included, an agreed amount, start date, and end date |

A single business **can** hold multiple simultaneous subscriptions (e.g.
POS System offline + School Management System online).

## 4. Data model

### staff_accounts
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | required |
| email | string | required, unique — used to log in |
| password | string (hashed) | required |
| is_active | boolean | default true — deactivating blocks login without deleting history |
| created_at / updated_at | timestamps | |

No role/permission field — every staff account has identical access in
phase 1 (see §2). `is_active` exists so a departing staff member can be
locked out while everything they created (businesses, subscriptions, etc.)
remains intact and correctly attributed.

### businesses
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | required |
| contact_person | string | optional |
| phone | string | optional |
| email | string | optional |
| address | string | optional |
| created_by | FK → staff_accounts | which staff member added this business |
| created_at / updated_at | timestamps | |

### services
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| name | string | required, e.g. "POS System" |
| description | text | optional |
| created_by | FK → staff_accounts | |
| created_at / updated_at | timestamps | |

### modules
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| service_id | FK → services | which service this module belongs to |
| name | string | required, e.g. "Inventory Management", "Payroll" |
| description | text | optional |
| created_at / updated_at | timestamps | |

A catalog of the building blocks staff can pick from when assembling a
business's package for a given service. This does **not** define pricing.

### packages
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| service_id | FK → services | which service this package belongs to |
| name | string | required, e.g. "Standard", "Premium" |
| description | text | optional |
| reference_price | decimal | suggested/starting price for this package — a guideline, not what's necessarily charged |
| created_at / updated_at | timestamps | |

A **reusable template** per service — the standard tiers most businesses
choose from. Not every business has to use one (see `subscriptions.package_id`
below).

> **How packages get defined, in practice:** packages should follow
> observed demand, not precede it. Don't invent tiers speculatively (e.g.
> forcing a "Premium" into existence just to have more than one option).
> Only turn a module combination into a named package once staff notice
> it being requested repeatedly — e.g. the third or fourth business asking
> for the same set of modules is the signal to formalize it as a package,
> so future entries for that combo are one click instead of picking
> modules individually. It's entirely normal to have just one package
> (or none) and let everything else stay custom indefinitely.

### package_modules (pivot)
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| package_id | FK → packages | required |
| module_id | FK → modules | required |

Defines which modules belong to a given package template, e.g. "Standard"
= Inventory + Reporting. Unique constraint on (package_id, module_id).

### service_pricing
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| service_id | FK → services | required |
| mode | enum(`online`,`offline`,`hybrid`) | required |
| base_price | decimal | list/reference price for this service+mode |
| created_at / updated_at | timestamps | |

Unique constraint: one row per (service_id, mode). **Reference only** — a
fallback guideline for staff quoting a fully custom (non-package) build.
`subscriptions.amount` is always the real, final agreed price.

### subscriptions
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| business_id | FK → businesses | required |
| service_id | FK → services | required |
| mode | enum(`online`,`offline`,`hybrid`) | required — which mode this business is on |
| package_id | FK → packages, nullable | which template package this was based on, if any — `null` for a fully custom build |
| amount | decimal | actual amount charged — may match the package's `reference_price` or be negotiated differently |
| start_date | date | required |
| end_date | date | required |
| notes | text | optional (e.g. install details, negotiation notes) |
| renewed_from_id | FK → subscriptions, nullable | set when this row is a renewal of an earlier subscription (see below) |
| created_by | FK → staff_accounts | who recorded this subscription |
| created_at / updated_at | timestamps | |

### subscription_modules (pivot)
| Field | Type | Notes |
|---|---|---|
| id | PK | |
| subscription_id | FK → subscriptions | required |
| module_id | FK → modules | required |

This pivot table holds the actual, final set of modules a business is
paying for under this subscription — whether that set came from a
`package_id` template or was built module-by-module for a custom deal.
When staff pick a package, its `package_modules` are copied in here as a
starting point (and can still be adjusted per business). Editing a
package template later does **not** retroactively change what existing
subscribers already have — each subscription's own row here is the source
of truth. Unique constraint on (subscription_id, module_id) to prevent
duplicates.

`status` is **not** stored — it's derived at query time from `end_date`
vs. today (see §7). This keeps it from ever going stale.

**Renewals are new rows, not edits.** When a business renews a service,
staff create a *new* subscription record rather than editing the
`end_date` of the old one. `renewed_from_id` links the new row back to the
one it replaces, forming a chain per business+service. When renewing, both
`package_id` and the module selection pre-fill from the previous
subscription, but remain fully editable — a business may upgrade to a
different package or tweak modules at renewal time. This preserves full
history and is what makes revenue trending (§8) possible — editing rows in
place would destroy that history. The old row simply becomes naturally
"expired" and stays in the table as a historical record.

## 5. CRUD operations by entity

Every entity needs its own management screens. This was missing from the
first draft — spelled out here explicitly.

| Entity | Create | Read | Update | Delete |
|---|---|---|---|---|
| **Staff accounts** | Any logged-in staff member can create a new staff account (no gatekeeping role in phase 1) | List all staff accounts | Edit name/email/password; toggle `is_active` | Not allowed — deactivate instead (`is_active = false`), to preserve `created_by` history everywhere |
| **Businesses** | Form: name, contact, phone, email, address | List, search, per-business detail page (§6 item 8) | Edit any field | Soft delete only (keep for historical subscriptions); block if it has subscriptions unless soft-deleted |
| **Services** | Form: name, description | List, per-service detail page (§6 item 9) | Edit name/description | Soft delete only if it has subscriptions; hard delete allowed if never used |
| **Modules** | Add a module under a service (name, description) | List modules per service | Edit name/description | Remove if not currently selected in any active subscription |
| **Packages** | Define a package under a service: name, description, reference_price, and which modules it includes | List packages per service, with their included modules | Edit name/description/price; add/remove included modules (does not retroactively affect existing subscribers, see §4) | Soft delete only if any subscription references it; hard delete if never used |
| **Service pricing** | Add a (mode, base_price) reference row per service | View per service | Edit base_price per mode | Remove a mode if unused by any subscription |
| **Subscriptions** | Form: business, service, mode, **either pick a package (auto-fills modules + suggested price) or build custom (pick modules individually)**, amount, start_date, end_date, notes | Master dashboard list (§6 item 6), filtered/sorted | Edit amount, dates, module selection, notes on an existing row (for correcting mistakes — **not** for renewals) | Soft delete only (for data-entry errors); never hard-delete a real historical record |

**Renewal is its own action**, distinct from "edit": a "Renew" button on
an existing subscription pre-fills a new subscription form (same business,
service, mode, same module selection, suggested new start/end dates),
lets staff adjust the modules and price, and sets `renewed_from_id` on
save, rather than opening the old row for editing.

## 6. Key features (phase 1)

1. **Staff account management** — create/deactivate staff logins (§5).
2. **Manage businesses** — create/edit/list/search businesses.
3. **Manage services, modules & packages** — create/edit services; define
   the module catalog per service (§5); define reusable package templates
   (e.g. "Standard", "Premium") that bundle specific modules with a
   reference price; maintain fallback reference pricing per mode for fully
   custom builds.
4. **Record a subscription** — select a business, select a service, select
   the mode, **either pick an existing package (auto-fills modules and a
   suggested price) or build a fully custom module selection**, enter the
   agreed amount, start date, end date (end date can be auto-suggested
   from start date + a term length, but remains editable).
5. **Renew a subscription** — dedicated "Renew" action that creates a new
   linked subscription row (with module selection carried over and
   editable) rather than editing the old one (§4, §5).
6. **Dashboard / master list** — table of all subscriptions across all
   businesses and services, showing: business name, service, mode,
   package/modules, amount, start date, end date, and a computed status
   badge.
7. **Expiry visibility** — sortable/filterable by status, with visual
   flags:
   - Active (comfortably within term)
   - Expiring soon (e.g. within 30 days of end_date — threshold configurable)
   - Expired (past end_date)
8. **Per-business view** — drill into a business to see all its
   subscriptions (past and current, including full renewal chain and each
   subscription's module selection).
9. **Per-service view** — drill into a service to see all businesses
   currently subscribed to it, and total revenue from it.
10. **Search/filter** — by business name, service, mode, status.
11. **Revenue trending reports** — see §8.

## 7. Status logic (computed, not stored)

```
if end_date < today:            status = "expired"
else if end_date - today <= 30: status = "expiring_soon"
else:                            status = "active"
```

The 30-day threshold should be a config value, not hardcoded, so it can be
tuned later.

## 8. Reporting & analytics (phase 1)

Now in scope for phase 1, built on top of the `subscriptions` table
(including its renewal chain via `renewed_from_id`):

1. **Revenue by service** — total and trending amount per service, so you
   can see e.g. "POS System" growing while another service is flat.
2. **Revenue by business** — which businesses generate the most revenue,
   and their trend over time (growing/shrinking spend as they renew at
   different amounts or add more services).
3. **Revenue by mode** — online vs. offline vs. hybrid, to see which
   delivery mode is gaining traction.
4. **Revenue over time** — monthly/quarterly totals (based on
   `start_date`, or recognized evenly across the subscription period —
   decide which during build), shown as a trend line.
5. **Renewal rate** — of subscriptions that reached their `end_date`,
   what percentage were renewed (have a `renewed_from_id` pointing to
   them) vs. lapsed.
6. **New vs. renewed revenue split** — how much revenue this period came
   from brand-new subscriptions vs. renewals of existing ones.
7. **Top gaining / top declining businesses** — businesses whose latest
   subscription amount is higher/lower than their previous one in the same
   renewal chain — directly answers "where are we gaining revenue and
   where are we losing it."
8. **Package popularity & custom-vs-standard split** — how many active
   subscriptions are on each package (e.g. "60% Standard, 25% Premium,
   15% custom") — useful for spotting upsell targets (custom/lower-tier
   businesses who might convert to a higher package) and for deciding
   which packages to promote.
9. **Most/least requested modules** — via `subscription_modules`, which
   modules appear most often across active subscriptions, per service —
   useful for spotting which features actually drive demand/price, and
   candidates for folding into a package template.

These can render as charts on the dashboard (e.g. line chart for revenue
over time, bar chart for revenue by service/business) alongside the master
subscriptions table.

## 9. Explicitly out of scope (phase 1)

- Automated email/SMS/WhatsApp reminders (confirmed not needed — the
  dashboard view itself is the "reminder")
- Client-facing login/portal
- Payment gateway integration / online checkout
- Software license enforcement inside the actual POS/school system products
- Multi-currency support (assume single currency unless stated otherwise)

These can be revisited in a later phase without changing the core schema.

## 10. Tech stack

- **Backend:** Laravel (PHP)
- **Frontend:** Vue (via Blade or Inertia — to be decided during build)
- **Database:** MySQL/MariaDB (Laravel default) or PostgreSQL

## 11. Resolved decisions (for reference)

- **No multi-role/permissions system in phase 1** — single flat staff
  access level; can be layered on later without a schema rewrite.
- **Renewals insert a new subscription row**, linked via
  `renewed_from_id`, rather than editing the existing row's `end_date`.
- **Revenue trending reporting is in phase 1**, not deferred.
- **Packages are a reusable catalog per service** (e.g. "Standard",
  "Premium"), each bundling specific modules with a reference price —
  businesses can also skip the catalog and get a fully custom module
  selection instead. A subscription's actual module set always lives in
  `subscription_modules` (copied from the package at selection time),
  so editing a package template later doesn't retroactively change what
  existing subscribers have.

## 12. Open questions for later phases

- Exportable reports (CSV/PDF) beyond on-screen charts?
- Multi-currency support, if the company expands beyond one currency?
- Role-based permissions, if the staff team grows large enough to need
  restricted access (e.g. finance-only views)?
