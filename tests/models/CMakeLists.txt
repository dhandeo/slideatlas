add_subdirectory( base )

#Style
add_midas_style_test( StyleSlideatlasModelsBase ${CMAKE_SOURCE_DIR}/modules/slideatlas/models/base/ )
add_midas_style_test( StyleSlideatlasModelsPdo ${CMAKE_SOURCE_DIR}/modules/slideatlas/models/pdo/ )
add_midas_style_test( StyleSlideatlasModelsDao ${CMAKE_SOURCE_DIR}/modules/slideatlas/models/dao/ )
