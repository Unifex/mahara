<?php
class ElasticsearchType_collection extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array ( // set to Page - used in 2nd facet
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'name' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'description' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'tags' => array (
                    'type' => 'keyword',
                    'copy_to' => ['tag','catch_all']
            ),
            'tag' => array (
                    'type' => 'keyword'
            ),
            // the owner can be owner (user), group, or institution
            'owner' => array (
                    'type' => 'long',
            ),
            'group' => array (
                    'type' => 'long',
            ),
            'institution' => array (
                    'type' => 'keyword',
            ),
            'access' => array (
                    'type' => 'object',
                    // public - loggedin - friends: if artefact is visible to public or logged-in users
                    // if public or logged, the other properties are ignored
                    'properties' => array (
                            'general' => array (
                                    'type' => 'keyword',
                            ), // array of institutions that have access to the artefact
                            'institutions' => array (
                                    'type' => 'keyword',
                                    'copy_to' => 'institution',
                            ),
                            'institution' => array (
                                    'type' => 'keyword',
                            ),
                            // array of groups that have access to the artefact
                            'groups' => array (
                                    'type' => 'object',
                                    'properties' => array (
                                        'all' => array (
                                            'type' => 'integer',
                                            'copy_to' => 'group',
                                        ),
                                        'admin' => array (
                                            'type' => 'integer',
                                            'copy_to' => 'group',
                                        ),
                                        'member' => array (
                                            'type' => 'integer',
                                            'copy_to' => 'group',
                                        ),
                                        'tutor' => array (
                                            'type' => 'integer',
                                            'copy_to' => 'group',
                                        )
                                    )
                            ),
                            'group' => array (
                                    'type' => 'integer'
                            ),
                            // array of user ids that have access to the artefact
                            'usrs' => array (
                                    'type' => 'integer',
                                    'copy_to' => 'usr',
                            ),
                            'usr' => array (
                                    'type' => 'integer'
                            )
                    )
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort' => array (
                    'type' => 'keyword',
            )
    );

    public static $mainfacetterm = 'Portfolio';
    public static $secfacetterm = 'Collection';
    public function __construct($data) {
        $this->conditions = array ();

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'secfacetterm' => NULL,
                'id' => NULL,
                'name' => NULL,
                'description' => NULL,
                'tags' => NULL,
                'owner' => NULL,
                'group' => NULL,
                'institution' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record) {
            return false;
        }
        $tags = get_column ('tag', 'tag', 'resourcetype', 'collection', 'resourceid', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
        // Access: get view_access info
        $access = self::collection_access_records ( $id );
        $accessObj = self::access_process ( $access );
        $record->access = $accessObj;
        $record->sort = strtolower ( strip_tags ( $record->name ) );
        $record->secfacetterm = self::$secfacetterm;
        return $record;
    }
    public static function getRecordDataById($type, $id) {
        $sql = 'SELECT c.id, c.name, c.ctime, c.description, cv.view AS viewid, c.owner, c.group
        FROM {collection} c
        LEFT OUTER JOIN {collection_view} cv ON cv.collection = c.id
        WHERE c.id = ?
        ORDER BY cv.displayorder asc LIMIT 1;';

        $record = get_record_sql ( $sql, array (
                $id
        ) );
        if (! $record) {
            return false;
        }

        $record->name = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->name ) );
        $record->description = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->description ) );

        // Created by
        if (intval ( $record->owner ) > 0) {
            $record->createdby = get_record ( 'usr', 'id', $record->owner );
            $record->createdbyname = display_name ( $record->createdby );
        }

        // Owned by group
        if (intval ( $record->group ) > 0) {
            $record->ownedbygroupurl = get_config('wwwroot') . 'group/view.php?id=' . $record->group;
            $record->ownedbygroupname = get_field('group', 'name', 'id', $record->group);
        }

        // Get all views included in that collection
        $sql = 'SELECT v.id, v.title
        FROM {view} v
        LEFT OUTER JOIN {collection_view} cv ON cv.view = v.id
        WHERE cv.collection = ?
        AND v.type != ?';

        $views = recordset_to_array ( get_recordset_sql ( $sql, array (
                $id, 'progress'
        ) ) );
        if ($views) {
            $record_views = array ();
            foreach ( $views as $view ) {
                if (isset ( $view->id )) {
                    $record_views [$view->id] = $view->title;
                }
            }
            $record->views = $record_views;
        }
        // Tags
        $tags = get_column('tag', 'tag', 'resourcetype', 'collection', 'resourceid', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
        return $record;
    }

    /**
     * Get all view access records relevant at the data of the indexing
     */
    public static function collection_access_records($id) {
        $records = get_records_sql_array ( '
                SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
                FROM {view_access} vac
                INNER JOIN {collection_view} vcol ON vac.view = vcol.view
                INNER JOIN {view} v ON v.id = vcol.view
                WHERE   vcol.collection = ?
                AND v.type != ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)', array (
                $id, 'progress'
        ) );

        return $records;
    }
}
