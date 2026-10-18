# Revised Philippine ECCD Checklist  
## Technical and Administration Manual

**Document type:** Official technical & administration guide  
**Source PDF:** Revised Philippine ECCD Checklist .pdf (25 pages)  
**Form covered here:** Form 2 — Ages **3 years 1 month to 5 years 11 months** (Child’s Record 2)

---

## 1. Testing Considerations

### Physical conditions
- Setting familiar to the child
- Preferably quiet, well lit, ventilated
- Table and chairs when possible; outdoors any flat surface is OK

### Standard procedures
Examiner may use:

1. Observation  
2. Direct elicitation from the child  
3. Interview of parent(s) / caregiver(s)  

Optional: ask caregivers what they do at home to stimulate development (record separately).

Older children (≥ ~2.6 years) may be tested in small groups of 2–3 if it improves responsiveness.

### Administration time
Each form should take **no more than 45 minutes**.

### Fundamentals
- Build rapport first (play/chat)
- Emphasize: **no right/wrong answers, no good/bad scores**
- Do **not teach** the correct answer (retesting later must remain valid)
- Write non-exact or notable answers in the **Comments** column

---

## 2. Testing Materials (Form 2)

Examples listed in the manual:

- Small toy, 2 balls  
- Food (bread, biscuit, raisin, wrapped candy)  
- Container with screw-on lid  
- Paper, crayon/pencil  
- Drinking cup and water  
- Picture book, 2 blocks, 2 spoons, cloth  
- Doll, toy car, shoestring, small shirt with button  
- 3 pairs different-colored crayons  
- 3 pairs picture cards  
- Shape pairs (same/different size & color)  
- Color papers, graduated circles/squares  
- 4–6 piece puzzle  
- Incongruous-activity picture cards  
- Carrying case  

---

## 3. Age Computation (official rule)
Age = Date Tested − Date of Birth
Each month = 30 days
Do NOT round years or months


Example from manual:

| | Year | Month | Day |
|--|------|-------|-----|
| Date of Exam | 2001 | 03 | 12 |
| Date of Birth | 1998 | 05 | 17 |
| **Age** | **2** | **09** | **25** |

→ Reported as **2.9 years** (no rounding).

---

## 4. Domains (Form 2)

1. Gross Motor  
2. Fine Motor  
3. Self-Help  
4. Receptive Language  
5. Expressive Language  
6. Cognitive  
7. Social-Emotional  

### How to administer items
- Follow **Materials/Procedure** column  
- Present → **✓** (check)  
- Not present → **—** (hyphen)  
- Gross Motor / Fine Motor / Self-Help: demonstration allowed when needed  
- Language / Cognitive / Social-Emotional: generally **do not demonstrate** unless specified  
- “Parental report will suffice” items may use caregiver report  
- “Must be elicited” items require examiner elicitation  
- If child tries and fails → score as not present  

### Where to start / stop
Administer **all** items on Form 2.

---

## 5. Scoring Pipeline
Raw score (count of ✓ per domain)
↓
Scaled score (lookup by age band + domain)
↓
Sum of 7 scaled scores
↓
Standard score (Development Index)
↓
Interpretation bands


---

## 6. Scaled Score Equivalent of Raw Scores

**Scaled scores range 1–19** (mean 10, SD ≈ 3).

### Age bands
| Band | Age |
|------|-----|
| 3.1 – 4.0 | 3y1m – 4y0m |
| 4.1 – 5.0 | 4y1m – 5y0m |
| 5.1 – 5.11 | 5y1m – 5y11m |

Tables map **raw score ranges → scaled score** per domain.  
(Full numeric tables are on pages 18–19 of the PDF; implemented in `EccdExpertSystem`.)

### Interpretation of scaled scores (Table 6)

| Scaled score | Interpretation |
|--------------|----------------|
| 1 – 3 | Suggest **significant delay** |
| 4 – 6 | Suggest **slight delay** |
| 7 – 13 | **Average** development |
| 14 – 16 | Suggest **slightly advanced** development |
| 17 – 19 | Suggest **highly advanced** development |

---

## 7. Standard Score (Development Index)

1. Add the 7 domain scaled scores → **Sum of Scaled Scores**  
2. Theoretical range of sum: about **7 – 133**  
3. Look up **Standard Score** in the conversion table (page 22)

### Sample mappings (from page 22)

| Sum of Scaled | Standard Score |
|---------------|----------------|
| 29 | 37 |
| 50 | 67 |
| 72 | 100 |
| 85 | 119 |
| 98 | 138 |

*(Complete table: sums 29→98 map to standards 37→138.)*

### Typical overall interpretation bands (aligned with form charts)

| Standard score (approx.) | Meaning |
|--------------------------|---------|
| ≤ ~69–79 | Delay / re-test guidance |
| ~80–119 | Average development |
| ≥ ~120–130+ | Advanced development |

Exact narrative bands used in LittleStep match the manual + Child’s Record 2 charts.

---

## 8. Plotting (same as Child’s Record 2)

- **Scaled Scores graph:** mark **x** per domain at the scaled value; connect  
- **Standard Scores graph:** mark **x** at the standard value by age column; connect across administrations  

---

## 9. Mapping to LittleStep Expert System

| Manual step | Code |
|-------------|------|
| Age (30-day months) | `EccdExpertSystem::computeAge()` |
| Raw = count of ✓ | `computeRawScores()` |
| Raw → Scaled tables | `getScaledScoreTables()` + `lookupScaledScore()` |
| Sum of scaled | `array_sum` of scaled scores |
| Sum → Standard | `convertToStandardScore()` |
| Interpretation | `interpret()` + `mapInterpretationCode()` |
| Persist | `saveScores()` → `domain_scores` |

### DB enum alignment (`interpretation_code`)

Preferred codes matching migration:

- `monitor_3m` / `monitor_6m` (delay / re-test guidance)  
- `average`  
- `slightly_advanced`  
- `highly_advanced`  

---

## 10. Implementation Checklist

- [x] Age bands 3.1–4.0, 4.1–5.0, 5.1–5.11  
- [x] 7 domains only (Form 2)  
- [x] ✓ / — as `check` / `hyphen`  
- [x] Scaled tables from pp. 18–19  
- [x] Standard table from p. 22  
- [x] Show page layout mirrors Child’s Record 2  
- [x] Charts auto-plot **x** from computed scores  

---

## 11. Related Documents

| File | Role |
|------|------|
| `ECCD_Childs_Record_2_Summary.md` | Form layout & fields |
| `LittleStep_Thesis_Summary.md` | Product/system context |
| `app/Services/EccdExpertSystem.php` | Executable rules |