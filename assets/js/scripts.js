let testScripts = (function ($) {
    let mapBox = {
        map: null,
        popup: null,
        markers: [],
        selectedTags: [],
        searchString: '',

        initMap: function () {
            mapboxgl.accessToken = initData.mapbox_access_token

            this.map = new mapboxgl.Map({
                container: "map",
                style: "mapbox://styles/mapbox/streets-v12",
                center: [-74.5, 40],
                zoom: 9,
            })

            this.loadMarkers()

            if (initData.is_user_logged_in) {
                $('.agentfire-test').on('submit', '#addMarkerForm', function (e) {
                    e.preventDefault()

                    let markerForm = $(this)

                    $.ajax(initData.api_endpoint + '/markers', {
                        type: 'POST',
                        data: markerForm.serialize(),
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader("X-WP-Nonce", initData.nonce)
                        },
                        success: function (data) {
                            if (data.success) {
                                mapBox.popup.remove()
                                if (data.is_new_tag_added) {
                                    mapBox.loadTags()
                                }
                                mapBox.loadMarkers(data.is_new_tag_added)
                            }
                        },
                    })
                })

                this.map.on('click', (e) => {
                    if (!e.originalEvent.target.classList.contains('mapboxgl-canvas')) return // prevent processing this handler when click on marker

                    let lngLat = e.lngLat
                    let contentHtml = '<h5>Add Marker</h5>' +
                        '<div class="content">' +
                        '<form id="addMarkerForm">' +
                        '<input type="hidden" name="lng" value="' + lngLat.lng + '"/>' +
                        '<input type="hidden" name="lat" value="' + lngLat.lat + '"/>' +
                        '<div class="form-group row">' +
                        '<label for="name" class="col-sm-12 col-form-label">Name</label>' +
                        '<div class="col-sm-12">' +
                        '<input type="text" class="form-control" name="name" placeholder="Name" required>' +
                        '</div>' +
                        '</div>' +
                        '<div class="form-group row mt-1">' +
                        '<label for="tags" class="col-sm-12 col-form-label">Tags</label>' +
                        '<div class="col-sm-12">' +
                        '<select class="select2-marker-tags" style="width:100%"  name="tags[]"></select>' +
                        '</div>' +
                        '</div>' +
                        '<div class="form-group row mt-3">' +
                        '<div class="col-sm-12">' +
                        '<button type="submit" class="btn btn-primary">Add</button>' +
                        '</div>' +
                        '</div>' +
                        '</form>' +
                        '</div>'

                    this.popup = new mapboxgl.Popup()
                        .setLngLat(lngLat)
                        .setHTML(contentHtml)
                        .addTo(this.map)

                    $('.select2-marker-tags').select2({
                        ajax: {
                            url: initData.api_endpoint + '/tags',
                            dataType: 'json',
                        },
                        selectionCssClass: 'test',
                        placeholder: 'Select tag',
                        multiple: true,
                        tags: true,
                        createTag: function (params) {
                            return {
                                id: params.term,
                                text: params.term,
                                newOption: true
                            }
                        },
                        templateResult: function (data) {
                            var $result = $("<span></span>")

                            $result.text(data.text)

                            if (data.newOption) {
                                $result.append(" <em>(new)</em>")
                            }

                            return $result
                        }
                    })
                })
            }

            $('#tags').on('change', function (e) {
                let selectedTags = []
                $('#tags input[type=checkbox]:checked').map(function (a, b) {
                    selectedTags.push(b.name)
                })
                mapBox.selectedTags = selectedTags

                mapBox.loadMarkers()
            })

            $('#currentUserMarkers').on('change', function (e) {
                mapBox.loadMarkers()
            })

            $('#search').on('change', function (e) {
                mapBox.searchString = e.target.value

                mapBox.loadMarkers()
            })
        },

        loadMarkers: function (isNewTagAdded) {
            isNewTagAdded = isNewTagAdded || false; //default value
            let markersUrl = initData.api_endpoint + '/markers'

            if (mapBox.selectedTags.length && !isNewTagAdded) {
                markersUrl += '?'

                mapBox.selectedTags.forEach(function (tag, index) {
                    markersUrl += ('tags[]=' + tag + ((index === mapBox.selectedTags.length - 1) ? '' : '&'))
                })
            }

            if (mapBox.searchString !== '') {
                markersUrl += (markersUrl.indexOf('?') === -1 ? '?' : '&') + 'search=' + mapBox.searchString
            }

            let isCurrentUserMarkers = $('#currentUserMarkers').is(':checked')
            if (isCurrentUserMarkers) {
                markersUrl += (markersUrl.indexOf('?') === -1 ? '?' : '&') + 'currentUserMarkers=1'
            }

            $.ajax(markersUrl, {
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", initData.nonce)
                },
                success: function (data) {
                    mapBox.renderMarkers(data)
                },
            })
        },

        renderMarkers: function (markers) {
            mapBox.markers.forEach(function (addedMarked) {
                addedMarked.remove()
            })
            mapBox.markers = []


            markers.forEach(function (marker) {
                let newMarker = new mapboxgl.Marker({color: marker.is_owner ? 'green' : 'blue'})
                    .setLngLat([marker.lng, marker.lat])
                    .setPopup(
                        new mapboxgl.Popup().setHTML(
                            '<h3>' + marker.name + '</h3>' +
                            '<p>Tags: ' + marker.tags + '</p>' +
                            '<p>Created: ' + marker.date + '</p>'
                        )
                    )
                    .addTo(mapBox.map)

                mapBox.markers.push(newMarker)
            })
        },

        loadTags: function () {
            $.ajax(initData.api_endpoint + '/tags', {
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", initData.nonce)
                },
                success: function (data) {
                    mapBox.renderTags(data.results)
                },
            })
        },

        renderTags: function (tags) {
            mapBox.selectedTags = []
            $('#tags').empty()

            tags.forEach(function (tag) {
                let el = document.createElement('div')
                el.className = 'form-check'

                el.innerHTML = '<input class="form-check-input" type="checkbox" name="' + tag["id"] + '" id="flexCheckDefault-' + tag["id"] + '">'
                    + '<label class="form-check-label" for="flexCheckDefault-' + tag["id"] + '">' + tag["text"] + '</label>'

                $('#tags').append(el)
            })
        },

        init: function () {
            mapBox.initMap()
        }
    }

    return {
        init: mapBox.init
    }
})(jQuery)
