<div id="users-view" class="panel panel-default">
    <div class="panel-body">

        {* tpl default/com_header plugin=users action=default.view *}

        <table class="table table-striped table-hover">
            <tbody>
                <tr>
                    <th>
                        {* lang name *}
                    </th>
                    <td>
                        {* var users.user_name *}
                    </td>
                </tr>
                <tr>
                    <th>
                        {* lang since *}
                    </th>
                    <td>
                        {* date users.user_since *}
                    </td>
                </tr>
                <tr>
                    <th>
                        {* lang laston *}
                    </th>
                    <td>
                        {* date users.user_laston *}
                    </td>
                </tr>
            </tbody><!--END table tbody-->
        </table><!--END table-->

    </div><!--END panel panel-body-->
</div><!--END panel-->