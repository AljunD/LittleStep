# LittleStep: A Learner Progress Monitoring System  
## at Barangay Balite Day Care Center

**Document type:** Undergraduate Capstone Project (BS Information Technology)  
**Institution:** New Era University — College of Informatics and Computing Studies  
**Authors:** Dalman, Aljun B. · Francisco, Christoper Jul P. · Llander, Michelle P.  
**Year:** 2026  
**Source PDF:** LittleStep_Chapters (1)-3.pdf (92 pages)

---

## 1. Project Overview

LittleStep is a **web + mobile** learner progress monitoring system for early childhood education at **Barangay Balite Day Care Center**. It replaces manual handwritten ECCD records with:

- A **teacher web portal** (Laravel) for recording domain evaluations
- A **guardian mobile app** (React Native) for viewing child progress
- A **rule-based expert system** that automates official Philippine ECCD Checklist scoring

---

## 2. Problem Statement

- Daycare centers still rely on paper forms and handwritten records
- Manual processes are time-consuming, hard to organize, and prone to loss/inconsistency
- Lack of structured storage and analytical tools makes progress evaluation difficult
- Teachers and parents need timely, accurate developmental information

---

## 3. Objectives

### General
Develop a monitoring application so teachers can efficiently **record, compute, and track** children’s developmental progress across ECCD domains.

### Specific
1. **Teacher web portal** — account management, domain recording, automated ECCD computations, performance reports  
2. **Guardian mobile app** — secure access to progress updates, domain scores, comments, and reports  
3. **Rule-based expert system** — age calculation, raw score tally, scaled/standard conversion, developmental interpretation  
4. **Evaluation** — usability, compatibility, functional performance  

---

## 4. Scope & Limitations

### In scope
- ECCD domain progress monitoring (7 domains)
- Automated age, raw → scaled → standard score computation
- Teacher data entry and guardian read-only views
- Auth, centralized MySQL storage, structured reports

### Out of scope
- Messaging, notifications, or announcements between teachers and guardians
- Non-ECCD academic grading systems

---

## 5. Technology Stack

| Layer | Technology |
|--------|------------|
| Mobile frontend | React Native (iOS + Android) |
| Backend / API | Laravel (RESTful API) |
| Database | MySQL |
| Architecture | Client–server |

---

## 6. System Roles

| Role | Interface | Main actions |
|------|-----------|--------------|
| **Teacher** | Web portal | Login → record domain evaluations → view automated summary → adjust teaching |
| **Guardian** | Mobile app | Login → select child → dashboard → view ECCD checklist / scores / reports |

---

## 7. Core Modules (from design chapters)

- **User & profile management** — teachers, guardians, children  
- **Progress tracking & ECCD module** — `ProgressRecord` → `Domain` → `DomainResult` → `DomainScore`  
- **Expert system** — age (30-day months), raw scores, scaled scores, standard score, interpretation  
- **Performance / reporting** — summarized outputs aligned with official ECCD forms  

---

## 8. Expert System (rule-based “AI”)

Not machine learning. Deterministic rules from the official Philippine ECCD Technical Manual:

1. **Age** — subtract DOB from test date; each month = 30 days; no rounding  
2. **Raw score** — count of ✓ (`check`) per domain  
3. **Scaled score** — lookup by age band (3.1–4.0, 4.1–5.0, 5.1–5.11) and domain  
4. **Sum of scaled scores** — sum of 7 domain scaled scores  
5. **Standard score** — lookup from sum  
6. **Interpretation** — official bands (delay / average / advanced)

Valid child age range for Child’s Record 2: **3 years 1 month – 5 years 11 months**.

---

## 9. Security Highlights (thesis)

- Rate limiting on sensitive endpoints (e.g. login)
- Secure REST APIs
- Role-based access (teacher vs guardian)
- Processing limited to ECCD computation and recommendations from existing progress data

---

## 10. Chapter Map (approx.)

| Chapter | Topic |
|---------|--------|
| 1 | Background, objectives, scope & limitations |
| 2 | Review of related literature (ECCD, monitoring systems) |
| 3 | Technical background (stack, architecture) |
| 4 | Analysis & design (user journeys, diagrams) |
| 5 | Implementation details (class/sequence diagrams, modules) |
| Later | Feasibility, security, development notes, conclusions |

---

## 11. Relevance to Implementation

This thesis is the product specification for the running LittleStep codebase:

- Option 2 DB design: **one `progress_records` row per child**
- Domains + results + domain_scores driven by the expert system
- Teacher web (`ProgressController`, Blade show form matching Child’s Record 2)
- Guardian mobile (`GuardianController` API + React Native Dashboard)