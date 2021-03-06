<div id="cache-control" class="panel panel-default">
    <div class="panel-body">

        <header>
            <section class="page-header clearfix">
                <h3 class="pull-left">
                    {* lang cache *} - {* lang control *}
                    <small>
                        - {* lang count *}: {* raw count *} - {* lang driver *}: {* raw driver *}
                    </small>
                </h3><!--END header page-header headline-->

                <div class="btn-group pull-right">
                    <a href="{* link cache/clear *}" class="btn btn-danger">
                        {* lang clear *}
                    </a>
                </div><!--END header page-header btn-group-->
            </section><!--END header page-header-->
        </header><!--END header-->

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>{* lang key *}</th>
                    <th>{* lang time *}</th>
                    <th class="text-center">{* lang size *}</th>
                </tr>
            </thead>

            <tbody>
                {* foreach entries *}
                <tr>
                    <td>{* var entries.name *}</td>
                    <td>{* date entries.time *}</td>
                    <td class="text-center">{* var entries.size *}</td>
                </tr>
                {* else entries *}
                <tr>
                    <th colspan="3" class="text-center">{* lang no_entry *}</td>
                </tr>
                {* endforeach entries *}
            </tbody>
        </table><!--END table-->

    </div><!--END panel panel-body-->
</div><!--END panel-->