# Note-App API Documentation

Base URL: `APP_URL` (e.g. `http://localhost`), all routes below are prefixed with `/api`.

## Authentication

This API uses **Laravel Passport** (OAuth2) for authentication.

1. Obtain an access token from Passport's OAuth token endpoint (`/oauth/token`) using the `password` grant type, with your `APP_PASSPORT_CLIENT_ID` / `APP_PASSPORT_CLIENT_SECRET` and the user's `email`/`password`.
2. Send the token on every protected request as a Bearer token header:

```
Authorization: Bearer <access_token>
```

Routes marked **Auth required** below use the `auth:api` middleware and will return `401 Unauthorized` without a valid token.

---

## Auth & Registration

### POST /register
Registers a new user and sends an email verification link.

**Body**

| Field    | Type   | Required | Notes                                              |
|----------|--------|----------|-----------------------------------------------------|
| email    | string | yes      | must be a valid, unique email                       |
| password | string | yes      | min 8 chars, mixed case, letters, numbers, symbols   |
| captcha  | string | yes      | captcha validation                                   |

**Response 200**
```json
{ "user": { "id": 1, "email": "user@example.com", "name": "user@example.com" } }
```

**Errors**
- `422` validation error, e.g. `{ "errors": { "email": ["..."] } }`

---

### GET /email/verify/{id}/{hash}
Verifies a user's email address via a signed link (sent by `/register`). Not intended to be called directly by the frontend — the link is clicked by the user.

- Validates the hash against the user's email and the URL signature/expiry.
- On success, marks the email as verified and redirects to `{APP_FRONTEND_URL}/login?verified=1`.

**Errors**
- `403` if the hash is invalid or the signature is invalid/expired.

---

### POST /email/verification-notification
**Auth required.** Resends the email verification link to the authenticated user. Rate-limited to 4 requests/minute (`throttle:4,1`).

**Response 200**
```json
{ "message": "Verifizierungslink wurde gesendet!" }
```

---

### POST /password/email
Sends a password reset link to the given email address.

**Body**

| Field | Type   | Required |
|-------|--------|----------|
| email | string | yes      |

**Response 200**
```json
{ "message": "We have emailed your password reset link." }
```

**Errors**
- `422` if the email is invalid or unknown, e.g. `{ "email": ["..."] }`

---

### POST /password/reset
Resets the user's password using the token received via `/password/email`.

**Body**

| Field                 | Type   | Required | Notes                              |
|-----------------------|--------|----------|--------------------------------------|
| token                 | string | yes      | token from the reset link            |
| email                 | string | yes      |                                       |
| password              | string | yes      | must match `password_confirmation`, follows default password rules |
| password_confirmation | string | yes      |                                       |

**Response 200**
```json
{ "message": "Your password has been reset." }
```

**Errors**
- `422` if the token/email combination is invalid or expired.

---

### GET /me
**Auth required.** Returns the currently authenticated user.

**Response 200**
```json
{ "id": 1, "name": "user@example.com", "email": "user@example.com", "email_verified_at": null }
```

---

### POST /logout
**Auth required.** Revokes the current access token.

**Response 200**
```json
{ "message": "Erfolgreich abgemeldet." }
```

---

## User

### PUT/PATCH /user/profile
**Auth required.** Updates the authenticated user's profile. All fields are optional (`sometimes`), but if present they are required and validated.

**Body**

| Field                 | Type   | Notes                                                          |
|-----------------------|--------|-----------------------------------------------------------------|
| name                  | string | max 255                                                          |
| email                 | string | max 255, must be unique (ignoring current user)                  |
| password              | string | must match `password_confirmation`, min 8, mixed case, letters, numbers, symbols |
| password_confirmation | string | required if `password` is present                               |

**Response 200**
```json
{ "message": "Profile updated successfully.", "user": { "id": 1, "name": "...", "email": "..." } }
```

**Notes**
- Changing the email resets `email_verified_at` to `null` and triggers a new verification email.

**Errors**
- `422` validation error.

---

## Pages

All page routes are **Auth required** and use the `auth:api` middleware. A page is identified by a `uuidSlug` (the first 36 characters of the string are treated as the page's UUID).

### GET /page/{uuidSlug}
Returns a single page by its UUID.

**Response 200**: the `Page` object.

**Errors**
- `403` if the user is not authorized to view the page (via `view` policy).
- `404` if no page with that UUID exists.

---

### GET /pages
Returns the full page tree (root pages, nested up to 3 levels of children) for the frontend sidebar/navigation.

**Response 200**: array of `Page` objects, each with nested `children`.

---

### POST /page
Creates a new page for the authenticated user.

**Body**

| Field     | Type    | Required | Notes                          |
|-----------|---------|----------|----------------------------------|
| title     | string  | yes      | max 255                          |
| icon      | string  | no       | max 255                          |
| order     | integer | no       |                                   |
| parent_id | integer | no       | must reference an existing page  |

**Response 201**: the created `Page` object.

---

### PUT/PATCH /page/{uuidSlug}
Updates a page's content/metadata.

**Body**

| Field     | Type    | Required | Notes                          |
|-----------|---------|----------|----------------------------------|
| title     | string  | no       | max 255                          |
| icon      | string  | no       | max 255                          |
| order     | integer | no       |                                   |
| parent_id | integer | no       | must reference an existing page  |
| content   | json    | no       | page body content                |

**Response 200**
```json
{ "message": "Seiteninhalt aktualisiert", "page": { ... } }
```

**Errors**
- `403` if the user is not authorized to update the page.

---

### DELETE /page/{uuidSlug}
Deletes a page.

**Response 200**
```json
{ "message": "Seite gelöscht" }
```

**Errors**
- `403` if the user is not authorized to delete the page.

---

### POST /page/{page}/move
Moves a page to a new parent and/or position.

**Body**

| Field     | Type    | Required | Notes                          |
|-----------|---------|----------|----------------------------------|
| parent_id | integer | no       | must reference an existing page  |
| order     | integer | no       |                                   |

**Response 200**
```json
{ "message": "Seite verschoben", "page": { ... } }
```

---

### POST /page/{page}/icon
Updates a page's icon.

**Body**

| Field | Type   | Required | Notes     |
|-------|--------|----------|-----------|
| icon  | string | yes      | max 255   |

**Response 200**
```json
{ "message": "Icon geändert", "page": { ... } }
```

---

## Favorites

All favorite routes are **Auth required**.

### GET /favorites
Returns the authenticated user's favorite pages.

**Response 200**: array of `Page` objects.

---

### POST /page/{uuidSlug}/favorite
Adds a page to the authenticated user's favorites.

**Response 201**
```json
{ "message": "Seite als Favorit gespeichert.", "page_id": 1 }
```

**Errors**
- `403` if the user is not authorized to view the page.
- `404` if no page with that UUID exists.

---

### DELETE /page/{uuidSlug}/favorite
Removes a page from the authenticated user's favorites.

**Response 200**
```json
{ "message": "Seite aus Favoriten entfernt.", "page_id": 1 }
```

**Errors**
- `403` if the user is not authorized to view the page.
- `404` if no page with that UUID exists.
