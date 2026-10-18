# Early Childhood Care and Development (ECCD) Checklist  
## Child’s Record 2 — Age 3 years 1 month to 5 years

**Document type:** Official assessment form (Child’s Record 2)  
**Age range:** 3 years 1 month – 5 years  
**Source PDF:** ECCD-Checklist-Child-s-Record-2 (1)-3.pdf (20 pages)  
**Issuing context:** Council for the Welfare of Children, DepEd, DOH, DSWD, UNICEF  
**Note:** First printing January 2004, Manila — Not for sale

---

## 1. Purpose of the Form

Paper form used by examiners/teachers to:

- Capture the child’s **sociodemographic profile**
- Record up to **three evaluations** (1st / 2nd / 3rd) on the same sheet
- Mark each skill as **present (✓)** or **not present (—)**
- Tally **raw scores** per domain
- Support conversion to **scaled** and **standard** scores (tables live in the Technical Manual)
- Plot **Scaled Scores** and **Standard Scores** graphs

---

## 2. Sociodemographic Profile (Page 1)

| Field | Notes |
|--------|--------|
| Child’s Name | |
| Sex | |
| Date of Birth | month / day / year |
| Address | Barangay, Municipality/City, Province, Region |
| Child’s Handedness | right · left · both · not yet established |
| Presently studying? | Yes / No + school / day care name |
| Father’s Name, Age, Occupation, Educational Attainment | |
| Mother’s Name, Age, Occupation, Educational Attainment | |
| Number of Siblings | |
| Birth Order | 1st, 2nd, 3rd, etc. |

---

## 3. Computation of the Child’s Age (Page 2)

**Rule (official):**

> After verifying the dates, compute the child’s age by subtracting the date he was born from the date the test was administered. **Each month is composed of 30 days. Do not round off the months or years.** Write the examiner’s name each time the test is administered.

Recommended: administer the Checklist **once a year**.

Form has three assessment blocks (1st / 2nd / 3rd), each with:

- Date Tested (Y / M / D)
- Child’s Date of Birth (Y / M / D)
- Child’s Age (Y / M / D)
- Examiner’s Name

---

## 4. Introducing the Checklist (Page 3)

### To parent/caregiver (summary)
- Goal is to see how the child is developing — **no pass/fail**
- Some items are for older children
- Do **not** coach the child; need true current ability
- Results will be shared later with stimulation suggestions

### To the child (summary)
- Some tasks easy, some hard
- Not expected to do everything
- Just try their best

### How to mark
| Result | Mark |
|--------|------|
| Skill/behavior present | **✓** (check) |
| Not present | **—** (hyphen) + optional comment |

### Scoring
- Administer **all** items  
- **Raw score** = count of ✓ in that domain → “Total Score”

---

## 5. Seven Developmental Domains

| # | Domain | Approx. items (Record 2) |
|---|--------|---------------------------|
| 1 | Gross Motor | 13 |
| 2 | Fine Motor | 11 |
| 3 | Self-Help | 27 |
| 4 | Receptive Language | 5 |
| 5 | Expressive Language | 8 |
| 6 | Cognitive | 21 |
| 7 | Social-Emotional | 24 |

Each item row has:

- Item number + activity description  
- Material / Procedure (how to elicit or when parental report suffices)  
- Present: **1st Eval | 2nd Eval | 3rd Eval**  
- Comments  

Footer: **TOTAL SCORE** per evaluation column.

---

## 6. Sample Gross Motor Items (Pages 4–5)

1. Climbs on chair or elevated furniture without help  
2. Walks backwards  
3. Runs without tripping or falling  
4. Walks down stairs, two feet on each step, one hand held  
5. Walks upstairs holding handrail, two feet on each step  
6. Walks upstairs alternate feet, no handrail  
7. Walks downstairs alternate feet, no handrail  
8. Moves body part as directed  
9. Jumps up  
10. Throws ball overhead with direction  
11. Hops one to three steps on preferred foot  
12. Jumps and turns  
13. Dances patterns / joins group movement activities  

*(Other domains follow the same table structure in the full form.)*

---

## 7. Score Summary Table (Page 18)

For each of the 7 domains, across 3 evaluations:

| | 1st Evaluation | 2nd Evaluation | 3rd Evaluation |
|--|----------------|----------------|----------------|
| | Raw · Scaled | Raw · Scaled | Raw · Scaled |
| Gross Motor … Social-Emotional | | | |
| **Sum of Scaled Scores** | | | |
| **Standard Score** | | | |
| **Interpretation** | | | |

Instructions on form:

1. Transfer raw score per domain  
2. Convert via **Scaled Score Equivalent of Raw Scores Table** (Technical Manual)  
3. Sum scaled scores  
4. Convert via **Standard Score Equivalent of Sums of Scaled Scores Table**  
5. Write child’s age on each evaluation  

---

## 8. Scaled Scores Chart (Page 19)

- Three panels (one per evaluation)  
- Rows: scaled score **19 → 1**  
- Columns: the 7 domains  
- Mark **x** on the dot for each domain’s scaled score; connect the x’s  
- Bands (visual):  
  - Suggests advanced development  
  - Average development  
  - Re-test after 3–6 months  

---

## 9. Standard Scores Chart (Page 20)

- Vertical scale roughly **160 → 20**  
- Columns by age group: **3 years & 1 month · 4 years · 5 years**  
- Mark **x** for each administration; connect over time  
- Bands:  
  - Suggests advanced development  
  - Average development  
  - Re-test after 3 to 6 months  
- Date Tested row at bottom  

---

## 10. Mapping to LittleStep Software

| Form concept | System implementation |
|--------------|------------------------|
| Sociodemographic profile | `children` + guardian/teacher tables |
| 1st / 2nd / 3rd evaluation | `domains.evaluation_round` = 1, 2, 3 |
| ✓ / — | `domain_results.present` = `check` / `hyphen` |
| Total Score (raw) | Count of `check` per domain per round |
| Age computation | Expert system (30-day months) |
| Scaled / Standard / Interpretation | `EccdExpertSystem` + `domain_scores` |
| Show page layout | `show.blade.php` + `domain-table` partial |

This form is the **visual and data contract** for the teacher “View Progress Record” screen and printable PDF output.