def add_audit_record(
    cursor,
    table_name,
    record_id,
    field_name,
    source_type,
    source_identifier,
    source_url,
    curator,
    action,
    notes=None
):
    sql = '''
        INSERT INTO curation_audit
        (table_name, record_id, field_name, source_type,
         source_identifier, source_url, curator,
         curation_action, notes)
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
    '''
    cursor.execute(sql, (
        table_name, record_id, field_name, source_type,
        source_identifier, source_url, curator,
        action, notes
    ))
