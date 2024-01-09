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
        case "login":
            Login();
            break;
        case "new":
            NewFile();
            break;
        case "getFiles":
            GetFiles();
            break;
        case "delete":
            DeleteFile();
            break;
        case "getFile":
            GetFile();
            break;
        case "saveAndRun":
            Save(true);
            break;
        case "checkSession":
            CheckSession();
            break;
        case "upload":
            Upload();
            break;
    }
} else {
    DefaultMethod();
}

function Register() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    if ($_POST["name"] == "") {
        $res["status"] = 1;
        $res["response"] = "Full name is empty.";
        exit(json_encode($res));
    }

    if ($_POST["email"] == "" || filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        $res["status"] = 1;
        $res["response"] = "The email you have entered is not valid.";
        exit(json_encode($res));
    }

    if ($_POST["password"] == "") {
        $res["status"] = 1;
        $res["response"] = "The password is empty.";
        exit(json_encode($res));
    }

    if ($_POST["password"] != $_POST["repassword"]) {
        $res["status"] = 1;
        $res["response"] = "The passwords do not match.";
        exit(json_encode($res));
    }

    if (strlen($_POST["password"]) < 4) {
        $res["status"] = 1;
        $res["response"] = "Your password is too short. Please have atleast 4 characters.";
        exit(json_encode($res));
    }

    if (FindIndex($data["users"], "email", $_POST["email"]) != -1) {
        $res["status"] = 1;
        $res["response"] = "There's already a user registered with that email.";
        exit(json_encode($res));
    }

    $newUser = [
        "id" => uniqid("user", false),
        "name" => $_POST["name"],
        "email" => $_POST["email"],
        "hash" => "",
        "avatar" => "uploads/default.jpg"
    ];

    $newUser["hash"] = PasswordStretch($newUser["id"], $_POST["password"]);
    $data["users"][] = $newUser;
    SetSiteData($data);
    $res["status"] = 0;
    $res["response"] = NewSession($newUser["id"]);
    exit(json_encode($res));
}

function Login() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userIndex = FindIndex($data["users"], "email", $_POST["email"]);

    if ($userIndex == -1) {
        $res["status"] = 1;
        $res["response"] = "Invalid login credentials.";
        exit(json_encode($res));
    }

    $user = $data["users"][$userIndex];

    if (PasswordStretch($user["id"], $_POST["password"]) != $user["hash"]) {
        $res["status"] = 1;
        $res["response"] = "Invalid login credentials.";
        exit(json_encode($res));
    }

    $res["status"] = 0;
    $res["response"] = NewSession($user["id"]);
    exit(json_encode($res));
}

function NewFile() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $content = "";

    switch ($_POST["language"]) {
        case "python":
            $content = <<<EOL
            def main():
                print("Hello, Python!")

            if __name__ == "__main__":
                main()
            EOL;

            break;
        case "javascript":
            $content = <<<EOL
            function main() {
                console.log("Hello, JavaScript!");
            }
            
            main();
            EOL;

            break;
        case "html":
            $content = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Blank HTML Project</title>
                </head>
                <body>
                    <h1>Hello, HTML!</h1>
                </body>
            </html>
            HTML;

            break;
        case "java":
            $content = <<<EOL
            public class Program {
                public static void main(String[] args) {
                    System.out.println("Hello, Java!");
                }
            }
            EOL;

            break;
        case "sql":
            $content = <<<SQL
            CREATE TABLE Users (
                UserID INT PRIMARY KEY,
                UserName VARCHAR(255)
            );
            
            INSERT INTO Users (UserID, UserName) VALUES (1, 'John');
            INSERT INTO Users (UserID, UserName) VALUES (2, 'Jane');
            
            SELECT * FROM Users;
            SQL;

            break;
    }

    $newFile = [
        "id" => uniqid("file"),
        "name" => "Untitled",
        "author" => $userId,
        "language" => $_POST["language"],
        "content" => $content,
        "time" => time()
    ];

    $data["files"][] = $newFile;
    SetSiteData($data);
    $res["status"] = 0;
    $res["response"] = $newFile["id"];
    exit(json_encode($res));
}

function GetFiles() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $result = [];

    foreach ($data["files"] as $key => $value) {
        if ($value["author"] == $userId) {
            $result[] = $value;
        }
    }

    $res["status"] = 0;
    $res["response"] = $result;
    exit(json_encode($res));
}

function DeleteFile() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $fileIndex = FindIndex($data["files"], "id", $_POST["file"]);

    if ($fileIndex == -1) {
        $res["status"] = 1;
        $res["response"] = "File not found.";
        exit(json_encode($res));
    }

    unset($data["files"][$fileIndex]);
    $data["files"] = array_values($data["files"]);
    SetSiteData($data);
    $res["status"] = 0;
    $res["response"] = "File successfully deleted.";
    exit(json_encode($res));
}

function GetFile() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $fileIndex = FindIndex($data["files"], "id", $_POST["file"]);

    if ($fileIndex == -1) {
        $res["status"] = 1;
        $res["response"] = "File not found.";
        exit(json_encode($res));
    }

    $file = $data["files"][$fileIndex];
    $res["status"] = 0;
    $res["response"] = $file;
    exit(json_encode($res));
}

function Save($isRun) {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $fileIndex = FindIndex($data["files"], "id", $_POST["file"]);

    if ($fileIndex == -1) {
        $res["status"] = 1;
        $res["response"] = "File not found.";
        exit(json_encode($res));
    }

    $file = $data["files"][$fileIndex];

    if ($file["author"] != $userId) {
        $res["status"] = 1;
        $res["response"] = "Unauthorized action.";
        exit(json_encode($res));
    }

    $data["files"][$fileIndex]["name"] = $_POST["name"];
    $data["files"][$fileIndex]["content"] = $_POST["content"];
    SetSiteData($data);

    if ($isRun) {
        $authData = [
            "clientId" => "8a9bfec4f5118076d2aad92aae8db0a2",
            "clientSecret" => "13d331b2c7bae0326131082abde0ea31d27543d0ef8b05fa4e8b236dde630e57"
        ];

        $token = SendCurl("https://api.jdoodle.com/v1/auth-token", "POST", ["Content-Type: application/json"], json_encode($authData));

        $executionData = [
            "script" => $_POST["content"],
            "language" => "",
            "versionIndex" => "0",
            "token" => $token
        ];

        switch ($file["language"]) {
            case "python":
                $executionData["language"] = "python3";
                break;
            case "javascript":
                $executionData["language"] = "nodejs";
                break;
            case "java":
                $executionData["language"] = "java";
                break;
            case "sql":
                $executionData["language"] = "sql";
                break;
            default:
                $res["response"] = [
                    "id" => $file["id"],
                    "output" => ""
                ];
        
                exit(json_encode($res));
        }

        $output = SendCurl("https://api.jdoodle.com/v1/execute", "POST", ["Content-Type: application/json"], json_encode($executionData));
        $output = json_decode($output, true);
        $output = $output["output"];
        $res["status"] = 0;

        $res["response"] = [
            "id" => $file["id"],
            "output" => $output
        ];

        exit(json_encode($res));
    }

    $res["status"] = 0;
    $res["response"] = $file["id"];
    exit(json_encode($res));
}

function CheckSession() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 0;
        $res["response"] = "0";
        exit(json_encode($res));
    }

    $res["status"] = 0;
    $res["response"] = NewSession($userId);
    exit(json_encode($res));
}

function Upload() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userId = AuthenticateUser($_POST["session"]);

    if ($userId == false) {
        $res["status"] = 1;
        $res["response"] = "Session expired.";
        exit(json_encode($res));
    }

    $newFile = [
        "id" => uniqid("file"),
        "name" => "Untitled",
        "author" => $userId,
        "language" => $_POST["language"],
        "content" => $_POST["content"],
        "time" => time()
    ];

    $data["files"][] = $newFile;
    SetSiteData($data);
    $res["status"] = 0;
    $res["response"] = $newFile["id"];
    exit(json_encode($res));
}

function DefaultMethod() {
    echo "Penis";
}

?>