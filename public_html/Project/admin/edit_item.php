<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");
$TABLE_NAME = "Products";
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    // die(header("Location: $BASE_PATH/home.php"));
    redirect("home.php");
}
//update the item
if (isset($_POST["submit"])) {
    if (update_data($TABLE_NAME, $_GET["id"], $_POST)) {
        flash("Updated item", "success");
    }
}

//get the table definition
$result = [];
$columns = get_columns($TABLE_NAME);
//echo "<pre>" . var_export($columns, true) . "</pre>";
$ignore = ["id", "modified", "created","averageratings"];
$db = getDB();
//get the item
$id = se($_GET, "id", -1, false);
$stmt = $db->prepare("SELECT * FROM $TABLE_NAME where id =:id");
try {
    $stmt->execute([":id" => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $result = $r;
    }
} catch (PDOException $e) {
    error_log(var_export($e, true));
    flash("Error looking up record", "danger");
}
//uses the fetched columns to map via input_map()
function map_column($col)
{
    global $columns;
    foreach ($columns as $c) {
        if ($c["Field"] === $col) {
            return input_map($c["Type"]);
        }
    }
    return "text";
}
?>
<div class="container-fluid">
    <h1>Edit Item</h1>
    <form method="POST" novalidate>
        <?php foreach ($result as $column => $value) : ?>
            <?php /* Lazily ignoring fields via hardcoded array*/ ?>
            <?php if (!in_array($column, $ignore)) : ?>
                <div class="mb-4">
                    <label class="form-label" for="<?php se($column); ?>"><?php se($column); ?></label>
                    <?php /*Allow toggling visibility using textbox*/ ?>
                    <?php if (str_contains(map_column($column), "checkbox")) : ?>
                        <?php /*will submit as value 0 if checkbox is not checked */ ?>
                        <input type="hidden" value="0" name="<?php se($column);?>"/>
                        <?php /*if visibility is 1 have input with checked attribute */ ?>
                        <?php if ($value==1) : ?>
                            <input class="form-check-input" id="<?php se($column); ?>" type="<?php echo map_column($column); ?>" value="1" name="<?php se($column); ?>" checked/>
                        <?php else : ?>
                            <input class="form-check-input" id="<?php se($column); ?>" type="<?php echo map_column($column); ?>" value="1" name="<?php se($column); ?>"/>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php if($column === "unit_price") : ?>
                            <input class="form-control" id="<?php se($column); ?>" type="<?php echo map_column($column); ?>" value="<?php se($value/100); ?>" name="<?php se($column); ?>" />
                        <?php else : ?>
                            <input class="form-control" id="<?php se($column); ?>" type="<?php echo map_column($column); ?>" value="<?php se($value); ?>" name="<?php se($column); ?>" />
                        <?php endif; ?>
                    <?php endif; ?> 
            <?php endif; ?>
        <?php endforeach; ?>
        <input class="btn btn-primary" type="submit" value="Update" name="submit" />
    </form>
    <div class="mt-4">
        <a href="list_items.php?">Return to list</a>
    </div>
</div>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");