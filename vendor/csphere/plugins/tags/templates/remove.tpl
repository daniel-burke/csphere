<div id="tags-remove" class="panel panel-default">
    <div class="panel-body">

        {* tpl default/com_header plugin=tags action=default.remove *}

        <div class="well text-center">
            <h4>{* lang default.remove_sure *}</h4>

            <br />

            <a href="{* link tags/remove/id/$rid$/sure/no *}" class="btn btn-default btn-lg btn-block">{* lang default.no *}</a>
            <a href="{* link tags/remove/id/$rid$/sure/yes *}" class="btn btn-danger btn-lg btn-block">{* lang default.yes *}</a>
        </div><!--END well-->

    </div><!--END panel panel-body-->
</div><!--END panel-->