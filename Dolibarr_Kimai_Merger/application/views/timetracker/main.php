<center>
<?php 
echo '<h2>'.$title.'</h2>';
//check if transfer on load in on.
//you can change this from timetracker's config.
if(isset($synced_customers))
{
    echo $synced_timesheets .' timesheets have been synced.<br/>'; 
    echo $synced_customers .' customers have been synced.<br/>'; 
    echo $synced_projects .' projects have been synced.<br/>'; 
    echo $synced_tasks .' tasks have been synced.<br/>';
    if($edited_timesheets)
    {
        echo '<h5>Edited Kimai timesheets that cannot be synced:</h5>';
        foreach($edited_timesheets as $timesheet)
        {
            echo 'Kimai id: '.intval($timesheet) . '<br>';
        }
        echo '<br/>';
    }
}
    ?>
    <br>
    <button id="transfer" type="button" class="btn btn-secondary">⏱️ Transfer timesheets to Dolibarr</button>
    <br>
    <p style="font-size:12px;">You should use this option if timetracker is installed in a new host to re-create the associations between
    Dolibarr and Kimai. This button will sync Users,Customers,Projects,Tasks but no timesheets.</p>
    <button id="restore" type="button" class="btn btn-secondary">📚 Restore assocications in database</button>
    <br>
    <div style="margin-top: 20px;">
        <img id="loading" src="<?php echo base_url('img/loading.gif')?>" style="display: inline-block; height: 50px; width: 50px;" >
        <img id="check" src="<?php echo base_url('img/tick.png')?>" style="display: inline-block; height: 30px; width: 30px;" >
    </div>
</center>
    
    
    

<script id="ajax_script" src="<?php echo base_url(); ?>/js/ajax/syncs.js" data-base_url="<?php echo base_url();?>">
</script>