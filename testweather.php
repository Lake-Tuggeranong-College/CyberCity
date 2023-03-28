<?php
include("template.php");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-8">
            <?php echo $deleteMsg??''; ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr>
                        <th>EventID</th>
                        <th>ModuleID</th>
                        <th>DateTime</th>
                        <th>Data</th>

                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData ORDER BY EventID DESC";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            $row_id = $row["EventID"];
                            $row_moduleID = $row["ModuleID"];
                            $row_dateTime = $row["DateTime"];
                            $row_data = $row["Data"];

                    }
                    ?>
                            <tr>

                                <td><?php echo $data['fullName']??''; ?></td>
                                <td><?php echo $data['gender']??''; ?></td>
                                <td><?php echo $data['email']??''; ?></td>
                                <td><?php echo $data['mobile']??''; ?></td>
                                <td><?php echo $data['address']??''; ?></td>
                                <td><?php echo $data['city']??''; ?></td>
                                <td><?php echo $data['state']??''; ?></td>
                            </tr>



                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>