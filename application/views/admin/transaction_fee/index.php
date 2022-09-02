<div class="page-wrapper">
	<div class="content container-fluid">
        <div class="container">
            <div class="row">
                <div class="col-sm-8">
                    <h4 class="page-title m-b-20 m-t-0">Transaction Fee</h4>
                </div>
                <div class="col-sm-4 text-right m-b-20">
                    <a href="<?php echo base_url($theme . '/transactionfee/create'); ?>" class="btn btn-primary add-button"><i class="fa fa-plus"></i></a>
                </div>
            </div>
            <?php
            if ($this->session->userdata('message')) {
                echo $this->session->userdata('message');
            }
            ?>
            <div class="panel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-actions-bar categories_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User Type</th>
                                    <th>Transaction Fee</th>
                                    <th>Created Date</th> -->
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $applyingAs = C_APPLINGAS;
                                asort($applyingAs, SORT_STRING);                               
                           

                                if (!empty($lists)) {
                                    $sno = 1;
                                    foreach ($lists as $row) {
                                        $_id = isset($row['id']) ? $row['id'] : '';
                                      
                                                                                 
                                ?>
                                            <tr>
                                                <td> <?php echo $sno ?></td>
                                                <td> <?php echo  $applyingAs[(int)$row['user_type']]; ?></td>
                                                
                                                <td> <?php echo $row['transaction_fee'] ?></td>
                                                <td> <?php echo $row['created_date']; ?></td>
                                                <td class="text-right">
                                                    <a href="<?php echo base_url().'admin/'.transactionfee.'/edit/' . $_id; ?>" class="btn btn-sm bg-success-light mr-2"><i class="fa fa-edit mr-1"></i> Edit</a>&nbsp;
                                                    <a href="javascript:void(0)" class="on-default remove-row btn btn-sm bg-danger-light mr-2 delete_item" id="Onremove_<?php echo $_id; ?>" data-id="<?php echo $_id; ?>"><i class="fa fa-trash-alt mr-1"></i> Delete</a>
                                                </td>
                                            </tr>
                                    <?php
                                        
                                        $sno = $sno + 1;
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="11">
                                            <p class="text-danger text-center m-b-0">No Records Found</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootbox.min.js"></script>
<script type="text/javascript">

    function delete_item(val) {
        bootbox.confirm("Are you sure want to Delete ? ", function(result) {
        if(result == true) {
          var url = base_url + 'admin/transactionfee/delete';
          var keyname = "<?php echo $this->security->get_csrf_token_name(); ?>";
          var keyvalue = "<?php echo $this->security->get_csrf_hash(); ?>";
          var tbl_id = val;
          var data = {
            tbl_id: tbl_id
          };
          data[keyname] = keyvalue;
          $.ajax({
            url: url,
            data: data,
            type: "POST",
            success: function(res) {
              if(res == 1) {
                window.location = base_url + 'admin/transaction';
              } else {
                window.location = base_url + 'admin/transaction';
              }
            }
          });
        }
      });
    }
    $(document).ready(function() {
      $('.delete_item').on('click', function() {
        var id = $(this).attr('data-id');
        delete_item(id);
      });
    });
</script>