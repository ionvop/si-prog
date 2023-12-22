<?php

include("common.php");
Debug();
header("Content-Type: application/json");
$_POST = json_decode(file_get_contents("php://input"), true);

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "register":
            Register();
            break;
    }
} else {
    DefaultMethod();
}

function Register() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "reason" => "",
        "displayMessage" => "",
        "redirect" => "",
        "response" => ""
    ];

    if ($_POST["name"] == "") {
        $res["status"] = 1;
        $res["reason"] = "Full name is empty.";
        exit(json_encode($res));
    }

    if ($_POST["email"] == "" || filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        $res["status"] = 1;
        $res["reason"] = "The email you have entered is not valid.";
        exit(json_encode($res));
    }

    if ($_POST["password"] == "") {
        $res["status"] = 1;
        $res["reason"] = "The password is empty.";
        exit(json_encode($res));
    }

    if ($_POST["password"] != $_POST["repassword"]) {
        $res["status"] = 1;
        $res["reason"] = "The passwords do not match.";
        exit(json_encode($res));
    }

    if (strlen($_POST["password"]) < 4) {
        $res["status"] = 1;
        $res["reason"] = "Your password is too short. Please have atleast 4 characters.";
        exit(json_encode($res));
    }

    if (FindIndex($data["users"], "email", $_POST["email"]) != -1) {
        $res["status"] = 1;
        $res["reason"] = "There's already a user registered with that email.";
        exit(json_encode($res));
    }

    $user = [
        "id" => uniqid("user", false),
        "name" => $_POST["name"],
        "email" => $_POST["email"],
        "hash" => "",
        "avatar" => "uploads/default.jpg"
    ];

    $user["hash"] = PasswordStretch($user["id"], $_POST["password"]);
    $data["users"][] = $user;
    SetSiteData($data);
    $res["status"] = 0;
    $res["redirect"] = "./";
    exit(json_encode($res));
}

function DefaultMethod() {
    echo "Penis";
}

?>