<?php

// Hold output key-value pairs
$output = array();

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0
    && $_FILES["file"]["type"] == "text/plain"
) {
    // File uploaded successfully

    // include DFA class
    require_once("include/dfa.php");

    // Open file
    if (!($file = fopen($_FILES['file']['tmp_name'], 'r'))) {
        exit_error("Server Error: Unable to open file! try again later.");
    }

    /*
     * Reading 1st line: Alphabets
     */
    $line = fgets($file);
    $tokens = explode(" ", $line);

    // Check first token type
    if (!ctype_digit($tokens[0]))
        exit_error("Error on line 1: Expected number as first token, but character found");

    $alphabet_count = intval($tokens[0]);
    $tokens_count = count($tokens);

    // Check alphabet count error
    if ($alphabet_count == 0)
        exit_error("Error on line 1: Alphabet count can't be zero!");

    // Read alphabet
    $alphabets = array();
    for ($i = 0, $j = 1; $i < $alphabet_count && $j < $tokens_count; $i++, $j++) {
        if (in_array($tokens[$j], $alphabets))
            exit_error("Error on line 1: Duplicated alphabet: Token no. " . ($i + 1));
        $alphabets[] = trim($tokens[$j]);
    }

    // Check if found alphabets are less than expected
    if ($alphabet_count != $i)
        exit_error("Error on line 1: Expected $alphabet_count alphabets but found $i alphabets.");

    /*
     * Reading 2nd line: States
     */
    $line = fgets($file);
    $tokens = explode(" ", $line);

    // Check first token type
    if (!ctype_digit($tokens[0]))
        exit_error("Error on line 2: Expected number as first token, but character found");

    $states_count = intval($tokens[0]);
    $tokens_count = count($tokens);

    // Check state count error
    if ($states_count == 0)
        exit_error("Error on line 2: States count can't be zero!");

    // Read states
    $states = array();
    $finals = array();
    $initial;
    $isInitial = false;
    $isFinal = false;

    for ($i = 0, $j = 1; $i < $states_count, $j < $tokens_count; $j++) {
        $token = trim($tokens[$j]);
        if ($token == "+") {
            $isFinal = true;
        } else if ($token == "-") {
            $isInitial = true;
        } else {
            if (in_array($token, $states))
                exit_error("Error on Line 2: Duplicated state: Token no. " . ($j + 1));
            $states[] = $token;
            if ($isFinal) {
                $finals[] = $token;
                $isFinal = false;
            }
            if ($isInitial) {
                $initial = $token;
                $isInitial = false;
            }

            $i++;
        }
    }

    // Check if found alphabets are less than expected
    if ($states_count != $i)
        exit_error("Error on line 2: Expected $states_count states but found $i states.");

    /*
     * Create DFA Object
     */
    $dfa = new dfa($states, $alphabets, $finals, $initial);

    // Reading lines of file
    $linec = 3;
    while ($line = fgets($file)) {
        $tokens = explode(" ", $line);
        try {
            $dfa->add_transition(trim($tokens[0]), trim($tokens[2]), trim($tokens[1]));
        } catch (Exception $exception) {
            exit_error("Error on Line $linec: {$exception->getMessage()}");
        }

        $linec++;
    }

    // Close file
    fclose($file);

    if (!$dfa->is_valid()) {
        exit_error("Invalid input DFA file. Some edges are omitted.");
    }

    /*
     * Here, We have a valid DFA and checked for all exceptions
     * We can perform minimization on DFA
     */
    $dfa->minimize();

    $output['status'] = 'success';

    /*
     * Create file and save minimized DFA
     */
    $filename = tempnam("files", "mdfa-");
    $output["f"] = basename($filename);
    $file = fopen($filename, "w");

    // 1st Line: Write Alphabets
    fwrite($file, count($dfa->get_alphabets()));
    foreach ($dfa->get_alphabets() as $alphabet)
        fwrite($file, " " . $alphabet);

    fwrite($file, "\r\n");
    
    // 2nd Line: Write States
    fwrite($file, count($dfa->get_states()));
    foreach ($dfa->get_states() as $state) {
        if($dfa->is_initial($state))
            fwrite($file, " -");
        if($dfa->is_final($state))
            fwrite($file, " +");
        fwrite($file, " " . $state);
    }

    // 3rd to nth Line: Write Transitions
    foreach ($dfa->get_states() as $state){
        foreach ($dfa->get_alphabets() as $alphabet) {
            fwrite($file, "\r\n" . $state . " " . $dfa->get_transition($state, $alphabet) . " " . $alphabet);
        }
    }

    fclose($file);

    $data = array();
    $data['file'] = "./download.php?file=" . explode(".", basename($filename))[0];
    foreach ($dfa->get_states() as $state) {
        $state = array('name' => $state, 'final' => $dfa->is_final($state), 'initial' => $dfa->is_initial($state));
        $data['states'][] = $state;
    }

    foreach ($dfa->get_alphabets() as $alphabet) {
        $data['alphabets'][] = $alphabet;
    }

    foreach ($dfa->get_states() as $state) {
        foreach ($dfa->get_alphabets() as $alphabet) {
            $data['transitions'][] = array('from' => $state, 'to' => $dfa->get_transition($state, $alphabet),
                'alphabet' => $alphabet);
        }
    }

    $data["finals"] = $dfa->get_finals();

    $output['data'] = $data;

    echo json_encode($output);

} else {
    if (!isset($_FILES['file'])) {
        exit_error("No file sent to server.");
    } else {
        if ($_FILES['file']['error'] != 0) {
            exit_error("File uploaded with error.");
        } else {
            exit_error("Invalid file type. only text files allowed.");
        }
    }
}

function exit_error($message)
{
    echo json_encode(array('status' => 'error', 'message' => $message));
    exit();
}

?>