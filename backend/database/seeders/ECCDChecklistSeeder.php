<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;

class ECCDChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $checklist = [

            // ========================== GROSS MOTOR DOMAIN (13) ==========================
            ['domain' => 'gross_motor', 'activity' => 'Climbs on chair or other elevated piece of furniture like a bed without help', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Walks backwards', 'materials_and_procedure' => 'MATERIAL: toy. PROCEDURE: Ask the child to walk backwards by demonstrating this. Credit if the child is able to walk backwards without falling and holding on to anything. Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Runs without tripping or falling', 'materials_and_procedure' => 'MATERIAL: ball. PROCEDURE: Encourage the child to run by rolling a ball across the floor. Credit if the child can run fast and smoothly without tripping or falling.'],
            ['domain' => 'gross_motor', 'activity' => 'Walks down stairs, two feet on each step, with one hand held', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Walks upstairs holding onto a handrail, two feet on each step', 'materials_and_procedure' => 'MATERIAL: toy. PROCEDURE: Place a toy in the middle of the step and ask the child to walk up the stairs to get the toy. Credit if the child walks up using the handrail... Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Walks upstairs with alternate feet without holding onto a handrail', 'materials_and_procedure' => 'MATERIAL: toy. PROCEDURE: Place a toy... Credit if the child walks upstairs alternating feet without holding support. Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Walks downstairs with alternate feet without holding onto a handrail', 'materials_and_procedure' => 'MATERIALS: toy. PROCEDURE: Place a toy... Credit if alternating feet without support. Parental report will suffice.'],
            ['domain' => 'gross_motor', 'activity' => 'Moves body part as directed', 'materials_and_procedure' => 'PROCEDURE: Ask the child to raise both arms. This must be elicited by the interviewer.'],
            ['domain' => 'gross_motor', 'activity' => 'Jumps up', 'materials_and_procedure' => 'This must be elicited by the interviewer.'],
            ['domain' => 'gross_motor', 'activity' => 'Throws ball overhead with direction', 'materials_and_procedure' => 'MATERIAL: ball. PROCEDURE: Give the child the ball... Credit if overhand throw within reach.'],
            ['domain' => 'gross_motor', 'activity' => 'Hops one to three steps on preferred foot', 'materials_and_procedure' => 'PROCEDURE: Ask the child to hop at least three times on preferred foot.'],
            ['domain' => 'gross_motor', 'activity' => 'Jumps and turns', 'materials_and_procedure' => 'PROCEDURE: Ask the child to jump while making a half-turn.'],
            ['domain' => 'gross_motor', 'activity' => 'Dances patterns/joins group movement activities', 'materials_and_procedure' => 'Parental report will suffice.'],

            // ========================== FINE MOTOR DOMAIN (11) ==========================
            ['domain' => 'fine_motor', 'activity' => 'Uses all five fingers to get food/toys placed on a flat surface', 'materials_and_procedure' => 'MATERIAL: small toy/object. PROCEDURE: Seat the child... Credit if using all five fingers as if raking.'],
            ['domain' => 'fine_motor', 'activity' => 'Picks up objects with thumb and index finger', 'materials_and_procedure' => 'MATERIAL: any small toy or food. PROCEDURE: Place toy/food... Credit if using thumb and index finger.'],
            ['domain' => 'fine_motor', 'activity' => 'Displays a definite hand preference', 'materials_and_procedure' => 'MATERIAL: toy. PROCEDURE: Place toy at midline... Parental report will suffice.'],
            ['domain' => 'fine_motor', 'activity' => 'Puts small objects in/out of containers', 'materials_and_procedure' => 'MATERIALS: small objects, container. This must be elicited by the interviewer.'],
            ['domain' => 'fine_motor', 'activity' => 'Holds crayon with all the fingers of his hand making a fist (i.e., palmar grasp)', 'materials_and_procedure' => 'MATERIAL: crayon. PROCEDURE: Present crayon... This must be elicited by the interviewer.'],
            ['domain' => 'fine_motor', 'activity' => 'Unscrews the lid of a container or unwraps food', 'materials_and_procedure' => 'MATERIALS: container with screw-on top or wrapped candy. This must be elicited by the interviewer.'],
            ['domain' => 'fine_motor', 'activity' => 'Scribbles spontaneously', 'materials_and_procedure' => 'MATERIALS: paper, pencil/crayon. PROCEDURE: Ask child to draw anything... Credit if purposeful marks.'],
            ['domain' => 'fine_motor', 'activity' => 'Scribbles vertical and horizontal lines', 'materials_and_procedure' => 'MATERIALS: paper, pencil/crayon. PROCEDURE: Demonstrate lines... Credit if at least 2 inches long.'],
            ['domain' => 'fine_motor', 'activity' => 'Draws circle purposefully', 'materials_and_procedure' => 'MATERIALS: paper, pencil/crayon. PROCEDURE: Demonstrate circle... Credit if closed or nearly closed curve.'],
            ['domain' => 'fine_motor', 'activity' => 'Draws a human figure (head, eyes, trunk, arms, hands/fingers)', 'materials_and_procedure' => 'MATERIALS: paper, pencil. PROCEDURE: Ask to draw a person. Credit if 3 or more body parts.'],
            ['domain' => 'fine_motor', 'activity' => 'Draws a house using geometric forms', 'materials_and_procedure' => 'MATERIALS: paper, pencil. PROCEDURE: Ask to draw a house. Credit if roof, main frame, and door/window.'],
            
            // ========================== SELF-HELP DOMAIN (27) ==========================
            ['domain' => 'self_help', 'activity' => 'Feeds self with finger food (e.g. biscuits, bread) using fingers', 'materials_and_procedure' => 'MATERIALS: bread, biscuits. This must be elicited by the interviewer.'],
            ['domain' => 'self_help', 'activity' => 'Feeds self using fingers to eat rice/viands with spillage', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Feeds self using spoon with spillage', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Feeds self using fingers without spillage', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Feeds self using spoon without spillage', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Eats without need for spoonfeeding during any meal', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Helps hold cup for drinking', 'materials_and_procedure' => 'Note: The cup should not have a lid or spout.'],
            ['domain' => 'self_help', 'activity' => 'Drinks from cup with spillage', 'materials_and_procedure' => 'Ask the caregiver if the child can drink from a cup/glass with some spillage.'],
            ['domain' => 'self_help', 'activity' => 'Drinks from cup unassisted', 'materials_and_procedure' => 'MATERIALS: drinking cup, water. This must be elicited by the interviewer.'],
            ['domain' => 'self_help', 'activity' => 'Gets drink for self unassisted', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Pours from pitcher without spillage', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Prepares own food/snack', 'materials_and_procedure' => 'Ask the caregiver if the child can prepare his own snack without help except for hard-to-reach items.'],
            ['domain' => 'self_help', 'activity' => 'Prepares meals for younger siblings/family members when no adult is around', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Participates when being dressed (e.g., raises arms or lifts leg)', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Pulls down gartered short pants', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Removes sando', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Dresses without assistance except for buttons and tying', 'materials_and_procedure' => 'Material: small shirt w/button and shoestring. Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Dresses without assistance including buttons and tying', 'materials_and_procedure' => 'PROCEDURE: Have the child demonstrate how to button and tie.'],
            ['domain' => 'self_help', 'activity' => 'Informs the adult only after he has already urinated or moved his bowels in his underpants', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Informs the adult of need to urinate or move bowels', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Goes to the designated place but sometimes still does this in his underpants', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Goes to the designated place and never does this in his underpants anymore', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Wipes/cleans self after a bowel movement', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Participates when bathing (e.g., rubbing arms with soap)', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'self_help', 'activity' => 'Washes and dries hands without any help', 'materials_and_procedure' => 'Ask the caregiver if the child can wash and dry hands without help...'],
            ['domain' => 'self_help', 'activity' => 'Washes face without any help', 'materials_and_procedure' => 'Ask the caregiver if the child can wash and dry face without help...'],
            ['domain' => 'self_help', 'activity' => 'Bathes without any help', 'materials_and_procedure' => 'Parental report will suffice.'],

            // ========================== RECEPTIVE LANGUAGE (5) ==========================
            ['domain' => 'receptive_language', 'activity' => 'Points to a family member when asked to do so', 'materials_and_procedure' => 'PROCEDURE: Ask the child to point to his mother/caregiver.'],
            ['domain' => 'receptive_language', 'activity' => 'Points to five body parts on himself when asked to do so', 'materials_and_procedure' => 'PROCEDURE: Have the child point to eyes, nose, mouth, hands and feet.'],
            ['domain' => 'receptive_language', 'activity' => 'Points to five named pictured objects when asked to do so', 'materials_and_procedure' => 'MATERIAL: picture book 1. PROCEDURE: Show picture book and ask “Where’s the ______?”'],
            ['domain' => 'receptive_language', 'activity' => 'Follows one-step instructions that include simple prepositions', 'materials_and_procedure' => 'MATERIAL: block/toy. PROCEDURE: Ask to put toy under/on/in...'],
            ['domain' => 'receptive_language', 'activity' => 'Follows two-step instructions that include simple prepositions', 'materials_and_procedure' => 'MATERIAL: block/toy. PROCEDURE: Get from under the table and place on the table.'],

            // ========================== EXPRESSIVE LANGUAGE (8) ==========================
            ['domain' => 'expressive_language', 'activity' => 'Uses five to 20 recognizable words', 'materials_and_procedure' => 'PROCEDURE: Ask caregiver if child can say five to six words aside from mama and papa.'],
            ['domain' => 'expressive_language', 'activity' => 'Uses pronouns (e.g. I, me, ako, akin)', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'expressive_language', 'activity' => 'Uses two- to three-word verb-noun combinations', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'expressive_language', 'activity' => 'Names objects in pictures', 'materials_and_procedure' => 'MATERIAL: picture book 2. PROCEDURE: Point to object and ask “Ano ‘to?” Credit if at least 4 objects.'],
            ['domain' => 'expressive_language', 'activity' => 'Speaks in grammatically correct two- to three-word sentences', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'expressive_language', 'activity' => 'Asks “what” questions', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'expressive_language', 'activity' => 'Asks “who” and “why” questions', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'expressive_language', 'activity' => 'Gives account of recent experiences (with prompting) in order of occurrence using past tense', 'materials_and_procedure' => 'PROCEDURE: Ask caregiver if child can recount recent experiences using past tense.'],

            // ========================== COGNITIVE DOMAIN (21) ==========================
            ['domain' => 'cognitive', 'activity' => 'Looks in the direction of fallen object', 'materials_and_procedure' => 'MATERIAL: spoon/ball. PROCEDURE: Drop object and observe if child looks down.'],
            ['domain' => 'cognitive', 'activity' => 'Looks for a partially hidden object', 'materials_and_procedure' => 'MATERIALS: ball, small towel. PROCEDURE: Partially hide ball...'],
            ['domain' => 'cognitive', 'activity' => 'Imitates behavior just seen a few minutes earlier', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'cognitive', 'activity' => 'Offers an object but will not release it', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'cognitive', 'activity' => 'Looks for a completely hidden object', 'materials_and_procedure' => 'MATERIALS: ball, small towel. PROCEDURE: Completely hide ball under towel.'],
            ['domain' => 'cognitive', 'activity' => 'Exhibits simple “pretend” play', 'materials_and_procedure' => 'MATERIALS: doll or toy car. PROCEDURE: Demonstrate pretend play.'],
            ['domain' => 'cognitive', 'activity' => 'Matches objects', 'materials_and_procedure' => 'MATERIALS: pairs of spoons, balls, blocks. PROCEDURE: Demonstrate matching.'],
            ['domain' => 'cognitive', 'activity' => 'Matches two to three colors', 'materials_and_procedure' => 'MATERIALS: Three pairs of crayons. PROCEDURE: Demonstrate color matching.'],
            ['domain' => 'cognitive', 'activity' => 'Matches pictures', 'materials_and_procedure' => 'MATERIALS: Three pairs of picture cards. PROCEDURE: Demonstrate picture matching.'],
            ['domain' => 'cognitive', 'activity' => 'Sorts based on shapes', 'materials_and_procedure' => 'MATERIALS: Four pairs of different shapes. PROCEDURE: Ask to put together same shapes.'],
            ['domain' => 'cognitive', 'activity' => 'Sorts objects based on two attributes (size and color)', 'materials_and_procedure' => 'MATERIALS: Four pairs same shape different size/color.'],
            ['domain' => 'cognitive', 'activity' => 'Arranges objects according to size from smallest to biggest', 'materials_and_procedure' => 'MATERIALS: Graduated squares and circles. PROCEDURE: Demonstrate ordering.'],
            ['domain' => 'cognitive', 'activity' => 'Names four to six colors', 'materials_and_procedure' => 'MATERIALS: Six papers of different colors. PROCEDURE: Ask “What color is this?”'],
            ['domain' => 'cognitive', 'activity' => 'Copies shapes', 'materials_and_procedure' => 'MATERIALS: paper and pen. PROCEDURE: Let child copy circle, triangle, square.'],
            ['domain' => 'cognitive', 'activity' => 'Names 3 animals or vegetables when asked', 'materials_and_procedure' => 'Credit if child can name 3 animals or vegetables.'],
            ['domain' => 'cognitive', 'activity' => 'States what common household items are used for', 'materials_and_procedure' => 'Credit if can state use of at least two items.'],
            ['domain' => 'cognitive', 'activity' => 'Can assemble simple puzzles', 'materials_and_procedure' => 'MATERIAL: Simple 4-6 piece puzzle. Allow 2 minutes.'],
            ['domain' => 'cognitive', 'activity' => 'Demonstrates an understanding of opposites', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'cognitive', 'activity' => 'Points to left and right sides of body', 'materials_and_procedure' => 'PROCEDURE: Ask to show left/right body parts. Credit if at least 5 correct.'],
            ['domain' => 'cognitive', 'activity' => 'Can state what is silly or wrong with pictures', 'materials_and_procedure' => 'MATERIALS: Two silly picture cards.'],
            ['domain' => 'cognitive', 'activity' => 'Matches upper case letters and matches lower case letters', 'materials_and_procedure' => 'MATERIALS: Alphabet cards. Credit if can match 4 pairs.'],
            
            // ========================== SOCIAL-EMOTIONAL DOMAIN (24) ==========================
            ['domain' => 'social_emotional', 'activity' => 'Enjoys watching activities of nearby people or animals', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Friendly with strangers but initially may show slight anxiety', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Plays alone but likes to be near familiar adults', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Laughs or squeals aloud in play', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Plays peek-a-boo', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Rolls ball interactively with caregiver', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Hugs or cuddles toys', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Demonstrates respect for elders using “po” and “opo”', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Shares toys with others', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Imitates adult activities', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Identifies feelings in others', 'materials_and_procedure' => 'Credit if child can tell when caregiver is happy, sad, etc.'],
            ['domain' => 'social_emotional', 'activity' => 'Appropriately uses cultural gestures of greeting', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Comforts playmates/siblings in distress', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Persists when faced with a problem', 'materials_and_procedure' => 'Credit if child tries to solve problem instead of crying.'],
            ['domain' => 'social_emotional', 'activity' => 'Helps with family chores', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Curious about environment but knows when to stop asking', 'materials_and_procedure' => 'Credit if child asks questions but knows when to stop.'],
            ['domain' => 'social_emotional', 'activity' => 'Waits for his turn', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Asks permission to play with toy being used by another', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Defends possessions with determination', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Plays organized group games fairly', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Can talk about complex feelings', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Honors a simple bargain with caregiver', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Watches responsibly over younger siblings', 'materials_and_procedure' => 'Parental report will suffice.'],
            ['domain' => 'social_emotional', 'activity' => 'Cooperates with adults and peers in group situations', 'materials_and_procedure' => 'Parental report will suffice.'],

        ];

        foreach ($checklist as $item) {
                    Domain::create([
                        'progress_record_id' => null,         
                        'domain'                 => $item['domain'],
                        'activity'               => $item['activity'],
                        'materials_and_procedure'=> $item['materials_and_procedure'],
                    ]);
                }

                $this->command->info('✅ ECCD Checklist seeded successfully! Total items: ' . count($checklist));
        }
}