<?php include "template.php";
/** @var $conn */ ?>


<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>

            <th>Date & Time</th>
            <th>Data</th>

        </thead>

        <?php
        if (isset($_GET["ModuleID"])) {
            $moduleToLoad = $_GET["ModuleID"];
        } else {
            header("location:moduleList.php");
        }

        $sql = "SELECT EventID, ModuleID, DateTime, Data FROM ModuleData WHERE ModuleID = " . $moduleToLoad . " ORDER BY EventID DESC";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $row_id = $row["EventID"];
                $row_moduleID = $row["ModuleID"];
                $row_dateTime = $row["DateTime"];
                $row_data = $row["Data"];

                echo "<title> Module ID:" . $row_moduleID . "</title>";
                echo "<tr>";

                echo "<td>" . $row_dateTime . "</td>";
                echo "<td>" . $row_data . "  </td>";
                echo "</tr>";

            }
            $result = null;
        }
        ?>
    </table>
</div>

<?php echo outputFooter(); ?>
</body>
</html>
