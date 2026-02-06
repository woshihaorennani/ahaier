        // Submit Contact function
        function submitContact() {
            const name = document.getElementById('contact_name').value;
            const phone = document.getElementById('contact_phone').value;
            const business = document.getElementById('contact_business').value;
            const region = document.getElementById('select_area').innerText; // Use innerText for div
            const request = document.getElementById('contact_request').value;

            if (!name || !phone || !business || region === '请选择所在区域' || !request) {
                alert('请填写完整信息');
                return;
            }
            
            if (!/^1[3-9]\d{9}$/.test(phone)) {
                alert('请输入正确的手机号码');
                return;
            }

            fetch(`${API_DOMAIN}/api/lottery/submitContact`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    business: business,
                    region: region,
                    request: request,
                    openid: openid
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.code === 1) {
                    alert('提交成功');
                    closeModal('contactModal');
                    // Reset form
                    document.getElementById('contact_name').value = '';
                    document.getElementById('contact_phone').value = '';
                    document.getElementById('contact_request').value = '';
                    document.getElementById('select_area').innerText = '请选择所在区域';
                } else {
                    alert(data.msg || '提交失败');
                }
            })
            .catch(error => {
                console.error('Error submitting contact:', error);
                alert('网络错误，请稍后重试');
            });
        }
