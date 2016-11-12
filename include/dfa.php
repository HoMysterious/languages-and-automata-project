<?php

/**
 * Class dfa
 * Abstract data type to store and validate dfa
 */
class dfa
{

    // hold states in array
    private $states;

    // Store alphabets
    private $alphabets;

    // Set of final states
    private $finals;

    // Starting state
    private $initial;

    // Transition Table
    private $transitions;

    function dfa($states, $alphabets, $finals, $initial)
    {
        $this->states = $states;
        $this->alphabets = $alphabets;
        $this->finals = $finals;
        $this->initial = $initial;

        $transitions = array();
    }

    public function get_states()
    {
        return $this->states;
    }

    public function get_alphabets()
    {
        return $this->alphabets;
    }

    public function get_finals()
    {
        return $this->finals;
    }

    public function get_initial()
    {
        return $this->initial;
    }

    public function is_final($state)
    {
        return in_array($state, $this->finals);
    }

    public function is_initial($state)
    {
        return $this->initial == $state;
    }

    /**
     * Add transition to transition table
     */
    public function add_transition($from, $alphabet, $to)
    {

        // Check if states and alphabet are valid
        if (!in_array($from, $this->states))
            throw new Exception("Invalid 'starting' state");

        if (!in_array($alphabet, $this->alphabets))
            throw new Exception("Invalid alphabet");

        if(!in_array($to, $this->states))
            throw new Exception("Invalid 'ending' state");

        $this->transitions[$from][$alphabet] = $to;
    }

    public function get_transition($from, $alphabet)
    {
        return $this->transitions[$from][$alphabet];
    }

    public function minimize()
    {
        $this->remove_unreachable_states();
        $this->remove_redundant_states();
    }

    /**
     * Remove states that we can't access from initial state
     */
    public function remove_unreachable_states()
    {
        $reachable_states = array($this->initial);
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($reachable_states as $state) {
                foreach ($this->alphabets as $alphabet) {
                    $reached = $this->get_transition($state, $alphabet);
                    if (!in_array($reached, $reachable_states)) {
                        $reachable_states[] = $reached;
                        $changed = true;
                    }
                }
            }
        }

        // Refine transition table to include just reachable states
        $new_transitions = array();
        foreach ($reachable_states as $state) {
            foreach ($this->alphabets as $alphabet) {
                $new_transitions[$state][$alphabet] = $this->transitions[$state][$alphabet];
            }
        }
        // Change DFA states set to be just reachable states and transition table to include just reachable states
        $this->transitions = $new_transitions;
        $this->states = $reachable_states;

        // Remove unreachable states from finals if exists one
        foreach ($this->finals as $key => $value) {
            if (!in_array($value, $this->states))
                unset($this->finals[$key]);
        }
    }

    /**
     * Remove redundant states
     * States that can be merged are redundant
     */
    function remove_redundant_states()
    {
        $states = $this->get_states();
        $alphabets = $this->get_alphabets();
        $finals = $this->get_finals();
        $table = array();

        // Initialize state-state table
        foreach ($states as $st1) {
            foreach ($states as $st2) {
                if (in_array($st1, $finals) == in_array($st2, $finals)) {
                    $table[$st1][$st2] = false;
                } else {
                    $table[$st1][$st2] = true;
                }
            }
        }

        $table_changed = true;
        while ($table_changed) {
            $table_changed = false;
            foreach ($states as $st1) {
                foreach ($states as $st2) {
                    if ($table[$st1][$st2] != true && $st1 != $st2) {
                        foreach ($alphabets as $alphabet) {
                            $state1_target = $this->get_transition($st1, $alphabet);
                            $state2_target = $this->get_transition($st2, $alphabet);
                            if ($table[$state1_target][$state2_target] == true) {
                                $table[$st1][$st2] = $table[$st2][$st1] = true;
                                $table_changed = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Extract and merge states using $table
        $groups = array();
        $state_group_map = array();
        $group_counter = 0;
        foreach ($states as $st1) {
            if (array_key_exists($st1, $state_group_map))
                continue;

            $state_group_map[$st1] = $group_counter;
            $groups[$group_counter][] = $st1;
            foreach ($states as $st2) {
                if ($table[$st1][$st2] == false && !array_key_exists($st2, $state_group_map)) {
                    $state_group_map[$st2] = $group_counter;
                    $groups[$group_counter][] = $st2;
                }
            }

            $group_counter++;
        }

        // Create new state names from groups
        $new_states = array();
        $new_finals = array();
        $new_initial = "";
        for ($i = 0; $i < $group_counter; $i++) {
            $new_states[$i] = implode("", $groups[$i]);

            if(in_array($this->initial, $groups[$i]))
                $new_initial = $new_states[$i];

            if(in_array($groups[$i][0], $this->finals))
                $new_finals[] = $new_states[$i];
        }

        // Create new transition table with merged states
        $new_transition = array();
        foreach ($new_states as $key => $value) {
            foreach ($alphabets as $alphabet) {
                $new_transition[$value][$alphabet] = $new_states[$state_group_map[$this->get_transition($groups[$key][0], $alphabet)]];
            }
        }

        // Assign new states and transition table to object
        $this->states = $new_states;
        $this->transitions = $new_transition;
        $this->finals = $new_finals;
        $this->initial = $new_initial;
    }

    public function is_valid()
    {

        foreach ($this->states as $state) {
            foreach ($this->alphabets as $alphabet) {
                if (!isset($this->transitions[$state][$alphabet]))
                    return false;
            }
        }

        return true;
    }

}