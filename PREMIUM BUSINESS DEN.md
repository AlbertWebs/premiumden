# PREMIUM BUSINESS DEN
## Master Development Prompt

You are building the complete **Premium Business Den** website and private membership portal.

Website/domain:

`https://premiumden.co.ke/`

Start this project **from scratch**, beginning with creating the Laravel application.

The application must feel like an **exclusive digital private business club**, not a generic membership website or a generic Laravel admin dashboard.

The defining product principle is:

> **Membership should feel like access, not just an account.**

Build the system from start to finish, including the public website, CMS, membership application and vetting system, payment architecture, private member portal, networking permissions, messaging, notifications, articles, authors, digital member cards, administration, security, responsive design and deployment readiness.

Do not stop after creating scaffolding. Continue through the implementation phases until the application is functional and testable.

---

# 1. TECHNOLOGY STACK

Use:

- Laravel
- Laravel Blade
- Alpine.js
- Tailwind CSS
- MySQL
- Laravel authentication
- Laravel Policies / Gates
- Laravel Notifications
- Laravel Mail
- Laravel Queues where appropriate
- Laravel Scheduler where appropriate
- Vite

Avoid unnecessary frontend frameworks.

Do NOT introduce React, Vue, Livewire or another major frontend framework unless there is a genuine technical requirement.

Blade + Alpine.js should provide the primary frontend experience.

Use modern Laravel conventions.

---

# 2. FIRST TASK – CREATE THE APPLICATION

Begin by creating the Laravel project.

Set up:

- Laravel application
- `.env`
- database configuration
- application name
- application URL
- timezone
- mail configuration placeholders
- queue configuration
- cache configuration
- session configuration
- Vite
- Tailwind CSS
- Alpine.js
- authentication
- migrations
- seeders
- factories
- storage linking
- application services

Use:

`APP_NAME="Premium Business Den"`

Production URL:

`https://premiumden.co.ke`

Use the appropriate Kenyan timezone.

Organize the code cleanly from the beginning.

Do not put complex business logic directly inside controllers.

Use appropriate:

- Services
- Actions
- Policies
- Form Requests
- Enums
- Events
- Listeners
- Notifications
- Jobs

where these improve maintainability.

---

# 3. DESIGN PHILOSOPHY

The application must look and feel extremely premium.

Think:

- private members club
- executive business society
- luxury hospitality
- high-end financial services
- understated exclusivity
- editorial business publication

Avoid:

- generic SaaS dashboard appearance
- oversized colorful cards
- excessive gradients
- childish icons
- excessive rounded corners
- clutter
- unnecessary animations
- generic Laravel styling

Use strong typography, disciplined spacing and elegant proportions.

The interface should communicate:

- exclusivity
- privacy
- prestige
- trust
- professionalism
- business networking
- recognition
- belonging

Whitespace is important.

Use subtle borders, shadows and surface changes.

Animation should be subtle and purposeful.

Interactions should feel polished through:

- smooth transitions
- hover states
- loading states
- success feedback
- elegant dropdowns
- modal transitions
- skeleton states where useful
- subtle page transitions
- polished form validation

Buttons, inputs, cards and navigation should share a consistent design language.

---

# 4. BUILD A DESIGN SYSTEM FIRST

Before producing dozens of screens, establish reusable Blade components.

Create components for:

- buttons
- inputs
- textarea
- select
- checkbox
- radio
- file upload
- cards
- badges
- membership badges
- avatars
- dropdowns
- modals
- alerts
- breadcrumbs
- pagination
- tabs
- tables
- empty states
- loading states
- confirmation dialogs
- navigation
- sidebar
- notification items
- member cards
- article cards
- status indicators

Create consistent variants such as:

- primary
- secondary
- subtle
- destructive
- success
- warning

Do not independently style every page.

---

# 5. RESPONSIVE DESIGN

Everything must work properly on:

- desktop
- laptop
- tablet
- mobile

The member portal is particularly important on mobile.

Design mobile navigation deliberately rather than simply shrinking desktop navigation.

Touch targets should be comfortable.

Forms must be easy to complete from phones.

---

# 6. ACCESSIBILITY

Implement:

- semantic HTML
- keyboard navigation
- focus states
- accessible labels
- adequate contrast
- ARIA attributes where necessary
- accessible dialogs
- accessible dropdowns
- descriptive validation errors

Premium design must not compromise usability.

---

# 7. USER ROLES

Implement roles and permissions.

Initial roles:

## Super Administrator

Full system access.

## Risk Team

Responsible for:

- application review
- vetting
- risk decisions
- applicant review

## Membership Administrator

Responsible for:

- applications
- documents
- invoicing
- payments
- membership activation
- membership renewals
- member administration

## Content Administrator

Responsible for:

- articles
- authors
- categories
- news
- events
- website content

## Author

Can create and manage permitted content.

## Member

Approved Premium Business Den member.

A Member additionally belongs to a membership package:

- Diamond
- Gold
- Platinum

Keep application roles separate from membership packages.

---

# 8. MEMBERSHIP PACKAGES

Create configurable membership packages.

Initial packages:

1. Diamond
2. Gold
3. Platinum

Do not hard-code everything into views.

Create a proper membership package model so administrators can eventually manage:

- name
- slug
- description
- price
- benefits
- status
- display order
- visual attributes
- renewal period
- networking level

---

# 9. CRITICAL MEMBERSHIP ACCESS RULES

This is one of the most important parts of the entire application.

## Diamond

Diamond members can:

- view Diamond members
- view Diamond profiles
- initiate chat with Diamond members

Diamond members CANNOT:

- view Gold members
- view Platinum members
- access Gold profiles
- access Platinum profiles
- initiate chat with Gold members
- initiate chat with Platinum members

Relationship:

`Diamond → Diamond`

---

## Gold

Gold members can:

- view Diamond members
- view Gold members
- view their profiles
- initiate conversations with Diamond members
- initiate conversations with Gold members

Gold members CANNOT:

- view Platinum members
- access Platinum profiles
- initiate conversations with Platinum members

Relationship:

`Gold → Gold + Diamond`

---

## Platinum

Platinum members can:

- view Diamond members
- view Gold members
- view Platinum members
- access all permitted member profiles
- initiate conversations with Diamond members
- initiate conversations with Gold members
- initiate conversations with Platinum members

Relationship:

`Platinum → Platinum + Gold + Diamond`

---

# 10. SECURITY OF MEMBERSHIP RULES

DO NOT implement these restrictions only by hiding frontend buttons.

Enforce them at:

- query level
- controller level
- Policy/Gate level
- route authorization level
- messaging service level

A Diamond member manually entering a Gold or Platinum member URL must NOT gain access.

A Gold member manually entering a Platinum profile URL must NOT gain access.

Create automated tests for these scenarios.

Prefer reusable authorization methods such as:

`canViewMember()`

`canMessageMember()`

and appropriate Laravel Policies.

---

# 11. PUBLIC WEBSITE

Build a complete public-facing Premium Business Den website.

Primary navigation:

- Home
- About
- Membership
- Membership Packages
- Benefits
- How to Become a Member
- Articles
- News & Activities
- Authors
- Contact
- Become a Member
- Member Login

The public website should establish credibility before asking visitors to join.

---

# 12. HOMEPAGE

Create a premium homepage.

Potential sections:

## Hero

Strong positioning statement.

Primary CTA:

`Become a Member`

Secondary CTA:

`Discover Premium Business Den`

## About Premium Business Den

Introduce the society.

## Membership

Introduce:

- Diamond
- Gold
- Platinum

## Why Join

Explain membership benefits.

## Business Community

Communicate the networking opportunity.

## How Membership Works

Visual overview of the application process.

## Latest Insights

Recent articles.

## Society Activity

Recent:

- events
- partnerships
- contract signings
- achievements
- announcements

## Final CTA

Invite qualified prospects to apply.

Do not invent factual claims, membership numbers, partners or statistics.

Use editable placeholders where real content has not been provided.

---

# 13. MEMBERSHIP APPLICATION WIZARD

Create a beautiful multi-step application wizard.

The business process is:

## STEP 01 – IDENTIFY TWO MEMBERS

The applicant identifies two existing Premium Business Den members who know them well.

Capture the references appropriately.

Where possible, references should map to existing members.

---

## STEP 02 – APPLICATION

Applicant visits:

`premiumden.co.ke`

Clicks:

`Become a Member`

Applicant completes the membership application.

They select:

- Diamond
- Gold
- Platinum

Capture relevant personal and professional information.

The form should support saving progress where appropriate.

After submission:

- create application record
- generate application reference
- send confirmation
- notify appropriate administrators

---

# 14. APPLICATION STATUS WORKFLOW

Use a proper state/status architecture.

Suggested flow:

`Draft`

→ `Submitted`

→ `Under Review`

→ `Vetting`

→ `Approved`

→ `Documents Required`

→ `Documents Received`

→ `Invoice Issued`

→ `Awaiting Payment`

→ `Paid`

→ `Membership Activated`

Alternative outcomes:

`Rejected`

`Suspended`

`Cancelled`

Maintain timestamps and audit information for important transitions.

---

# 15. STEP 03 – RISK VETTING

Submitted applications must undergo mandatory vetting by the Risk Team.

Risk Team interface should allow:

- application review
- references review
- internal notes
- risk notes
- supporting information
- positive outcome
- negative outcome
- request additional information

Sensitive internal risk information must NEVER be exposed to the applicant/member.

Keep internal notes separate from member-visible communication.

---

# 16. STEP 04 – DOCUMENTS

After approval, request:

- Passport-size photograph
- Travel passport OR national ID

Build secure document uploads.

Validate:

- file types
- size
- authorization

Do not expose sensitive documents through public storage URLs.

Administrators should be able to:

- review document
- approve
- reject
- request replacement

Track document status.

---

# 17. STEP 05 – INVOICE & PAYMENT

After document approval, issue the membership invoice.

Create models for:

- invoices
- invoice items
- payments
- payment attempts
- payment references
- payment status

Statuses should include appropriate states such as:

- pending
- processing
- paid
- failed
- cancelled
- refunded where relevant

Build the payment system behind an abstraction/service interface.

Do not tightly couple membership logic to one payment provider.

For example:

`PaymentGatewayInterface`

Then provider implementation.

This allows payment integrations to change later.

Implement webhook/callback architecture securely.

Requirements:

- verify callbacks
- prevent duplicate processing
- idempotent payment confirmation
- log payment attempts
- never trust browser redirect alone
- activate membership only after verified payment

If final payment provider credentials are unavailable, create the complete integration architecture with a safe sandbox/mock implementation and clearly mark the configuration point.

---

# 18. STEP 06 – MEMBERSHIP ACTIVATION

Once:

- application approved
- documents accepted
- payment verified

activate membership.

Generate:

- membership number
- membership record
- membership start date
- membership status
- package
- digital membership card record

Send member:

- welcome notification
- membership confirmation
- login/activation instructions

The provided business process references a one-time password.

Implement a secure first-access mechanism.

Do not store plaintext OTPs.

Use expiration.

OTP should be single-use.

After first access, require secure password setup if appropriate.

---

# 19. WELCOME EXPERIENCE

Do not simply redirect newly activated members to a generic dashboard.

Create a premium onboarding experience.

Example:

`Welcome to Premium Business Den, Albert.`

Display:

- membership package
- membership number
- benefits
- profile completion
- member directory introduction
- messaging explanation
- digital member card
- important society information

The user should immediately feel that membership has value.

---

# 20. MEMBER DASHBOARD

Create an elegant member dashboard.

Possible content:

- personalized greeting
- membership package
- membership status
- digital member card preview
- profile completion
- unread messages
- recent notifications
- upcoming events
- latest articles
- recent society announcements
- suggested permitted members
- membership benefits

Avoid dashboard clutter.

Prioritize information.

---

# 21. MEMBER PROFILE

Each approved member should have a professional profile.

Fields can include:

- profile photograph
- full name
- membership number
- membership package
- company
- position/title
- industry
- business category
- location
- business interests
- professional biography
- membership status
- joined date

Provide editing controls for appropriate fields.

Administrators may control protected fields such as:

- membership number
- package
- status

---

# 22. MEMBER DIRECTORY

Build a premium searchable member directory.

Allow filtering where appropriate by:

- name
- industry
- company
- business category
- location
- package, where permitted

Member cards should feel elegant.

Example information:

- photograph
- name
- title
- company
- industry
- package badge
- View Profile
- Message

But actions must respect permissions.

IMPORTANT:

Do not fetch forbidden members and merely hide them in Blade.

Filter unauthorized members at query level.

---

# 23. MEMBER MESSAGING

Create private member-to-member messaging.

Implement:

- conversations
- conversation participants
- messages
- unread counts
- timestamps
- read status
- conversation list
- latest message preview
- message notifications

Keep architecture ready for future realtime functionality.

Initial implementation does not have to require WebSockets if that unnecessarily increases complexity.

Alpine.js can provide a polished interaction experience.

Membership permissions MUST apply before a conversation can be initiated.

Once membership status changes or becomes inactive, access should be re-evaluated appropriately.

---

# 24. NOTIFICATIONS

Build an in-app notification center.

Notifications can include:

- new messages
- application updates
- membership approval
- document requests
- payment updates
- membership activation
- society announcements
- events
- business opportunities
- contract signings
- partnerships
- membership notices
- article recommendations

Support notification targeting.

Administrators should be able to target:

- everyone
- Diamond
- Gold
- Platinum
- Gold + Platinum
- selected members

Use database notifications.

Email important notifications where appropriate.

Architecture should allow SMS/push notifications to be added later.

---

# 25. DIGITAL MEMBER CARD

Members receive physical chipped membership cards.

Create a digital representation in the portal.

Display:

- Premium Business Den branding
- member photograph where appropriate
- member name
- membership number
- package
- membership status
- validity where applicable

Create a secure unique identifier for the membership/card.

Prepare architecture for future:

- NFC/chip association
- QR verification
- physical card identifier
- card activation
- card replacement
- card status

Do NOT claim that NFC functionality exists until actual hardware integration has been implemented.

---

# 26. PHYSICAL WELCOME PACKAGE

Track the Premium Welcome Gift Box.

Package contains:

- Member Card
- USB Plugin Pen
- Brochure

Create optional administration fields for:

- package prepared
- dispatched
- collected/delivered
- date
- tracking/reference
- notes

This helps administrators know whether an activated member has received the physical membership package.

---

# 27. ARTICLES

Build a complete article/content system.

Models:

- Article
- Category
- Tag
- Author

Article fields:

- title
- slug
- excerpt
- body
- featured image
- author
- category
- tags
- status
- published_at
- SEO title
- SEO description
- OG image where appropriate

Statuses:

- draft
- pending review
- published
- archived

---

# 28. AUTHOR PORTAL

Approved authors should be able to sign in.

Authors can:

- create article
- edit their article
- upload featured image
- assign categories
- assign tags
- save draft
- preview
- submit for review

Authors should NOT automatically have unrestricted CMS access.

Content administrators can:

- review
- edit
- approve
- reject
- publish
- archive

---

# 29. SOCIETY NEWS & ACTIVITIES

Create content types/sections for:

- contract signings
- partnerships
- business collaborations
- member achievements
- events
- meetings
- society announcements
- corporate engagements
- community initiatives

Allow administrators to manage these through the CMS.

---

# 30. EVENTS

Create event support.

Fields may include:

- title
- description
- location
- start date/time
- end date/time
- featured image
- visibility
- registration/invite information
- status

Visibility could support:

- public
- all members
- selected packages
- invite only

Do not overbuild event ticketing unless required.

---

# 31. CMS

Build a clean administration CMS.

CMS should manage:

- homepage
- about page
- membership page
- packages
- benefits
- FAQs
- articles
- authors
- categories
- tags
- news
- activities
- events
- announcements
- contact information
- SEO metadata

Avoid requiring developers to edit Blade files for routine website content.

---

# 32. ADMIN DASHBOARD

Create a purpose-built Premium Business Den admin experience.

Dashboard metrics can include:

- total active members
- Diamond members
- Gold members
- Platinum members
- pending applications
- applications under vetting
- awaiting documents
- awaiting payments
- recent payments
- articles pending approval
- recent members

Do not overuse charts.

Use them only when useful.

---

# 33. APPLICATION MANAGEMENT

Administrators need a dedicated application management area.

Provide filters:

- status
- package
- application date
- vetting result
- payment state

Application detail should show a timeline.

Example:

Application submitted  
↓  
Risk review started  
↓  
Approved  
↓  
Documents requested  
↓  
Documents verified  
↓  
Invoice issued  
↓  
Payment received  
↓  
Membership activated

This should be one of the strongest admin experiences.

---

# 34. AUDIT LOG

Because the platform handles vetting, payments and membership decisions, create an audit trail.

Record important actions such as:

- application status changes
- vetting decisions
- package changes
- document decisions
- invoice creation
- payment confirmation
- membership activation
- suspension
- admin changes
- role changes
- card status changes

Record:

- actor
- action
- affected resource
- timestamp
- useful metadata

Do not log passwords, OTP values or sensitive secrets.

---

# 35. SEARCH

Implement useful search.

Admin search should support relevant records such as:

- members
- applications
- membership numbers
- email
- phone
- invoice reference

Member search should respect directory permissions.

Public search can focus on articles/content if needed.

---

# 36. EMAILS

Create branded Premium Business Den email templates.

Important emails:

- application received
- application status update
- additional information requested
- application approved
- application unsuccessful
- documents requested
- invoice issued
- payment confirmed
- membership activated
- welcome email
- first-login/OTP email
- membership renewal notice
- new message notification where appropriate
- important society announcement

Emails must match the premium brand.

---

# 37. WELCOME EMAIL

The welcome email should include:

- member name
- confirmation of membership
- package
- membership number
- portal link
- key benefits
- instructions for first access
- support/contact information

Tone:

Professional, warm and exclusive.

Avoid over-the-top luxury language.

---

# 38. CONTACT SYSTEM

Build contact form functionality.

Capture:

- name
- email
- phone
- subject
- message

Store enquiries in CMS/admin.

Provide statuses such as:

- new
- contacted
- resolved

Implement spam protection/rate limiting.

---

# 39. SEO

Public website must be SEO-ready.

Implement:

- semantic markup
- configurable title
- meta description
- canonical URL
- Open Graph
- social preview
- sitemap
- robots configuration
- article structured data where appropriate
- organization structured data where appropriate
- clean slugs
- proper headings
- optimized images

Private member/admin pages should not be indexed.

---

# 40. SECURITY

Security is critical.

Implement:

- CSRF protection
- secure authentication
- authorization policies
- password hashing
- secure OTP handling
- rate limiting
- login throttling
- secure uploads
- MIME validation
- server-side validation
- protection against IDOR
- protection against mass assignment
- escaped output
- safe rich text handling
- webhook verification
- idempotent payments
- secure session configuration
- audit logs

Never expose:

- ID/passport documents
- internal vetting notes
- private member data
- private messages
- payment secrets

through public URLs.

---

# 41. PRIVACY

Build privacy into the system.

Provide appropriate:

- Privacy Policy
- Terms
- Cookie notice where applicable
- consent fields
- communication preferences

Allow members to control appropriate public/profile information while keeping membership-level restrictions authoritative.

---

# 42. DATABASE DESIGN

Design the schema carefully before implementing controllers.

Expected entities may include:

- users
- roles
- permissions
- membership_packages
- membership_applications
- application_references
- application_status_history
- vetting_reviews
- applicant_documents
- members
- memberships
- membership_status_history
- invoices
- invoice_items
- payments
- payment_attempts
- member_cards
- welcome_packages
- conversations
- conversation_participants
- messages
- notifications
- articles
- authors
- categories
- tags
- article_tag
- events
- announcements
- activities/news
- contact_enquiries
- audit_logs
- settings

Normalize sensibly.

Do not create unnecessary tables simply because they appear in this list.

Use foreign keys and indexes appropriately.

---

# 43. SEED DATA

Create development seeders.

Create test accounts for:

## Administrator

Super admin.

## Diamond Member

Active membership.

## Gold Member

Active membership.

## Platinum Member

Active membership.

## Risk Officer

## Content Administrator

## Author

Seed sample:

- articles
- events
- notifications
- applications
- membership packages

Clearly document development credentials.

Never use seeded development credentials in production.

---

# 44. AUTOMATED TESTING

Write tests for critical business logic.

Especially test:

### Diamond

Can view Diamond.

Cannot view Gold.

Cannot view Platinum.

Can message Diamond.

Cannot message Gold.

Cannot message Platinum.

### Gold

Can view Diamond.

Can view Gold.

Cannot view Platinum.

Can message Diamond.

Can message Gold.

Cannot message Platinum.

### Platinum

Can view all packages.

Can message all packages.

Also test:

- unauthorized profile URL access
- conversation creation
- application transitions
- document authorization
- payment idempotency
- membership activation
- admin authorization
- author permissions

Use feature tests heavily for business workflows.

---

# 45. APPLICATION JOURNEY TEST

The system must support this complete scenario:

`Visitor`

→ Become a Member

→ Choose package

→ Identify two members

→ Complete application

→ Submit

→ Confirmation received

→ Risk Team reviews

→ Application approved

→ Documents requested

→ Applicant uploads photo + ID/passport

→ Documents verified

→ Invoice generated

→ Payment initiated

→ Payment verified

→ Membership activated

→ Membership number generated

→ Digital card generated

→ Welcome email sent

→ OTP/first access sent

→ Member signs in

→ Completes profile

→ Enters member dashboard

→ Browses permitted members

→ Initiates permitted conversation

→ Receives notifications

This complete flow must be tested before declaring the application ready.

---

# 46. UI STATES

Every important interface must account for:

- loading
- empty
- success
- warning
- validation failure
- server failure
- unauthorized
- no search results
- disabled state

Do not leave blank screens.

Empty states should explain what the user can do next.

---

# 47. PERFORMANCE

Optimize:

- database queries
- eager loading
- indexes
- pagination
- images
- asset bundling
- caching where appropriate

Avoid N+1 queries, particularly:

- member directory
- messages
- notifications
- articles
- admin dashboards

---

# 48. MEDIA

Create a clean media strategy.

Use optimized images.

Generate/display appropriate sizes where possible.

Support:

- profile photos
- article images
- event images
- website content imagery
- OG images

Use fallbacks where media is missing.

---

# 49. ADMIN UX

Admin pages should be efficient.

Use:

- searchable tables
- useful filters
- bulk actions only where safe
- clear status badges
- pagination
- confirmation dialogs
- activity timelines
- contextual actions

Avoid forcing administrators through excessive modal windows.

---

# 50. MEMBER UX

Member portal navigation:

- Dashboard
- My Profile
- Members
- Messages
- Notifications
- Articles
- Events
- Membership
- Member Card
- Benefits
- Settings
- Logout

Navigation should adapt intelligently to device size.

Do not display inaccessible features unnecessarily.

---

# 51. URL STRUCTURE

Use clean URLs.

Examples:

Public:

`/`

`/about`

`/membership`

`/membership/diamond`

`/membership/gold`

`/membership/platinum`

`/how-to-join`

`/articles`

`/articles/{slug}`

`/events`

`/contact`

`/become-a-member`

Member:

`/member/dashboard`

`/member/profile`

`/member/directory`

`/member/directory/{member}`

`/member/messages`

`/member/messages/{conversation}`

`/member/notifications`

`/member/membership`

`/member/card`

Admin:

`/admin`

`/admin/applications`

`/admin/members`

`/admin/payments`

`/admin/content`

etc.

Use route names consistently.

---

# 52. DEVELOPMENT PHASES

Work systematically.

## PHASE 1 – Foundation

- create Laravel project
- configure environment
- install frontend dependencies
- authentication
- design system
- database architecture
- roles
- permissions
- package architecture

Validate before proceeding.

---

## PHASE 2 – Public Website

Build:

- layout
- navigation
- homepage
- about
- membership
- packages
- benefits
- how to join
- articles
- activities
- events
- contact
- authentication entry points

---

## PHASE 3 – CMS

Build:

- CMS dashboard
- pages/content
- articles
- authors
- categories
- tags
- events
- announcements
- settings

---

## PHASE 4 – Membership Application

Build:

- application wizard
- references
- package selection
- submission
- application status
- confirmation
- admin application management

---

## PHASE 5 – Risk & Documents

Build:

- risk review
- notes
- decisions
- document request
- secure uploads
- document review

---

## PHASE 6 – Payments

Build:

- invoices
- payment service
- payment attempts
- callback/webhook handling
- verification
- idempotency
- payment status
- membership activation trigger

---

## PHASE 7 – Member Portal

Build:

- onboarding
- dashboard
- profile
- membership information
- digital card
- benefits
- notifications

---

## PHASE 8 – Networking

Build:

- member directory
- package-based visibility
- profiles
- conversations
- messaging
- unread state
- notification integration

---

## PHASE 9 – QA & SECURITY

Test:

- responsive layouts
- permissions
- application workflow
- payments
- messaging
- content
- authentication
- security
- error states

---

## PHASE 10 – DEPLOYMENT READINESS

Prepare:

- production environment requirements
- `.env.example`
- queue worker instructions
- scheduler
- storage
- migrations
- caching
- build commands
- deployment documentation
- backup considerations

Do not run destructive production operations without explicit approval.

---

# 53. THREE-DAY IMPLEMENTATION PRIORITY

We are working under a compressed development schedule.

Prioritize functional vertical slices over isolated screens.

## DAY 1

Focus:

- Laravel setup
- database
- authentication
- roles
- packages
- CMS
- APIs
- application workflow
- public UX/UI
- articles
- admin foundation

Goal:

**ADMIN WORKS**

---

## DAY 2

Focus:

- payment architecture/integration
- invoices
- activation
- member dashboard
- profiles
- directory
- membership permissions
- notifications
- digital member card
- onboarding
- messaging foundation

Goal:

**MEMBER WORKS**

The complete flow should work:

`Application → Approval → Documents → Invoice → Payment → Activation → Login → Portal`

---

## DAY 3

Focus:

- messaging completion
- notifications
- author workflow
- remaining frontend
- mobile optimization
- permission testing
- security
- UX refinement
- email templates
- SEO
- QA
- deployment preparation

Goal:

**EVERYTHING WORKS TOGETHER**

System should be ready for client review the following day.

---

# 54. WORKING STYLE

Do not blindly generate large amounts of code.

At the beginning of each major phase:

1. Inspect the existing application.
2. Understand what has already been implemented.
3. Determine dependencies.
4. Implement the smallest coherent vertical slice.
5. Run migrations/tests/build.
6. Fix errors.
7. Continue.

Do not duplicate functionality already present.

Do not replace good existing work unnecessarily.

Preserve established design language.

---

# 55. CODE QUALITY

Use:

- descriptive naming
- small controllers
- Form Requests
- Policies
- Services/Actions for complex workflows
- Enums for stable states
- transactions for critical workflows
- Events/Listeners where decoupling helps
- clear relationships
- proper validation
- PHP type declarations where appropriate

Avoid:

- giant controllers
- business logic in Blade
- repeated permission logic
- repeated queries
- magic strings everywhere
- hard-coded IDs
- duplicated UI

---

# 56. DO NOT INVENT BUSINESS INFORMATION

Do not fabricate:

- member counts
- partners
- testimonials
- pricing
- addresses
- phone numbers
- statistics
- event information
- executives
- benefits not specified

Where content is missing, create clearly identifiable editable placeholder content.

---

# 57. FINAL ACCEPTANCE CHECKLIST

Do not consider the project ready until these are verified.

## Public Website

- responsive
- premium
- CMS driven
- articles working
- events/news working
- SEO implemented
- application CTA working

## Application

- package selection
- references
- forms
- submission
- status tracking
- risk review
- documents
- invoice
- payment
- activation

## Membership

- onboarding
- profile
- dashboard
- membership status
- digital card
- benefits

## Permissions

- Diamond → Diamond
- Gold → Gold + Diamond
- Platinum → Platinum + Gold + Diamond

Verified server-side.

## Networking

- directory
- profiles
- messaging
- unread state
- notifications

## Content

- authors
- articles
- categories
- tags
- approvals
- publishing

## Administration

- applications
- vetting
- members
- packages
- payments
- content
- notifications
- events
- settings
- audit history

## Quality

- desktop
- tablet
- mobile
- validation
- empty states
- loading states
- security
- authorization
- automated tests
- deployment readiness

---

# 58. FINAL PRODUCT VISION

Never lose sight of what we are building.

Premium Business Den is not:

> A website with a login area.

It is:

> **A private digital business society.**

The public website should create aspiration.

The application process should communicate selectivity.

The vetting process should communicate trust.

The onboarding experience should communicate achievement.

The member portal should communicate belonging.

The directory should create valuable connections.

The membership hierarchy should create structured access.

The content platform should establish business authority.

The physical and digital membership cards should reinforce identity.

Every screen should support the feeling that the member belongs to a carefully curated professional community.

Build accordingly.

---

# 59. START NOW

Begin from the current project directory.

If no Laravel application exists, create one first.

Then:

1. Initialize Laravel.
2. Configure the project.
3. Establish the database architecture.
4. Establish authentication.
5. Build the reusable design system.
6. Implement roles and membership packages.
7. Seed Diamond, Gold and Platinum.
8. Implement the membership application workflow.
9. Build the CMS/admin foundation.
10. Build the public website.
11. Continue through the remaining phases in dependency order.

After every major implementation phase:

- run relevant tests
- run migrations where required
- run/build frontend assets
- inspect for errors
- fix regressions
- verify responsive behavior
- update documentation

Do not merely describe what needs to be built.

**Implement it.**
